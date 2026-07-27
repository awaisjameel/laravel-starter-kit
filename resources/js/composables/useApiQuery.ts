import type { ComputedRef, Ref } from 'vue'
import type { ApiError } from './useApiClient'
import { normalizeApiError } from './useApiClient'

export type ApiCacheKey = string | string[]
type ApiCacheKeyInput = ApiCacheKey | Ref<ApiCacheKey> | ComputedRef<ApiCacheKey> | (() => ApiCacheKey)

type QueryCacheEntry<TData> = {
    data: TData
    updatedAt: number
}

type QueryCacheRevision = {
    epoch: number
    key: number
}

type ErrorMapper<TError> = (error: unknown) => TError
type IsSameType<TLeft, TRight> = [TLeft] extends [TRight] ? ([TRight] extends [TLeft] ? true : false) : false

interface UseApiQueryBaseOptions<TData, TError> {
    key: ApiCacheKeyInput
    queryFn: () => Promise<TData>
    enabled?: boolean | Ref<boolean> | ComputedRef<boolean> | (() => boolean)
    staleTimeMs?: number
    retry?: number
    retryDelayMs?: number
    mapError?: ErrorMapper<TError>
}

interface UseSelectedApiQueryOptions<TData, TSelected, TError> extends UseApiQueryBaseOptions<TData, TError> {
    select: (data: TData) => TSelected
    initialData?: TSelected
}

interface UseIdentityApiQueryOptions<TData, TError> extends UseApiQueryBaseOptions<TData, TError> {
    select?: undefined
    initialData?: TData
}

interface UseApiMutationOptions<TVariables, TResult, TError = ApiError, TContext = unknown> {
    mutationFn: (variables: TVariables) => Promise<TResult>
    invalidateKeys?: ApiCacheKey[]
    onMutate?: (variables: TVariables) => Promise<TContext> | TContext
    onSuccess?: (result: TResult, variables: TVariables, context: TContext | undefined) => Promise<void> | void
    onError?: (error: TError, variables: TVariables, context: TContext | undefined) => Promise<void> | void
    onSettled?: (result: TResult | undefined, error: TError | null, variables: TVariables, context: TContext | undefined) => Promise<void> | void
    mapError?: ErrorMapper<TError>
}

const DEFAULT_STALE_TIME = 30_000
const DEFAULT_RETRY_COUNT = 1
const DEFAULT_RETRY_DELAY_MS = 300

// Query results are per-visitor data. The SSR process is long-lived and shared by
// every request, so this module-level cache must stay empty there — otherwise one
// user's response would be served to the next request that renders the same key.
// The same reasoning keeps `useToast` client-only and Pinia per-app-instance.
//
// Entries hold the raw `queryFn` result, never a `select` projection, so one cache
// entry can serve consumers that project the same key differently.
const queryCache = new Map<string, QueryCacheEntry<unknown>>()

// Requests currently in flight, keyed exactly like `queryCache`. Two components that
// mount with the same key share one network request instead of racing two. Empty
// under SSR for the same reason as `queryCache`: `execute` returns before it is read.
const inFlightRequests = new Map<string, Promise<unknown>>()
const queryCacheRevisions = new Map<string, number>()
let queryCacheEpoch = 0

const isServerRendering = (): boolean => import.meta.env.SSR === true

const currentQueryCacheRevision = (cacheKey: string): QueryCacheRevision => ({
    epoch: queryCacheEpoch,
    key: queryCacheRevisions.get(cacheKey) ?? 0
})

const isCurrentQueryCacheRevision = (cacheKey: string, revision: QueryCacheRevision): boolean =>
    revision.epoch === queryCacheEpoch && revision.key === (queryCacheRevisions.get(cacheKey) ?? 0)

const invalidateQueryCacheKey = (cacheKey: string): void => {
    queryCacheRevisions.set(cacheKey, (queryCacheRevisions.get(cacheKey) ?? 0) + 1)
    queryCache.delete(cacheKey)
    inFlightRequests.delete(cacheKey)
}

/**
 * Runs `request` at most once per cache key at a time, handing every concurrent
 * caller the same promise. Only the raw fetch is shared: `select` and `mapError`
 * stay per caller, so joining never leaks one consumer's projection into another.
 */
const dedupeRequest = async <TData>(cacheKey: string, request: () => Promise<TData>): Promise<TData> => {
    const pendingRequest = inFlightRequests.get(cacheKey) as Promise<TData> | undefined

    if (pendingRequest !== undefined) {
        return pendingRequest
    }

    const startedRequest = request()
    inFlightRequests.set(cacheKey, startedRequest)

    try {
        return await startedRequest
    } finally {
        // Only retire our own entry. An invalidation between start and settle may
        // already have dropped it and let a newer request take the slot.
        if (inFlightRequests.get(cacheKey) === startedRequest) {
            inFlightRequests.delete(cacheKey)
        }
    }
}

const toCacheKey = (key: ApiCacheKey): string => {
    if (Array.isArray(key)) {
        return JSON.stringify(key)
    }

    return key
}

const wait = async (durationMs: number): Promise<void> => {
    await new Promise((resolve) => {
        setTimeout(resolve, durationMs)
    })
}

const resolveEnabled = (enabled: UseApiQueryBaseOptions<unknown, unknown>['enabled']): boolean => {
    if (enabled === undefined) {
        return true
    }

    return Boolean(toValue(enabled as never))
}

const mapErrorWith = <TError>(mapper: ErrorMapper<TError> | undefined, error: unknown): TError => {
    if (mapper !== undefined) {
        return mapper(error)
    }

    return normalizeApiError(error) as TError
}

const isCacheFresh = (updatedAt: number, staleTimeMs: number): boolean => {
    return Date.now() - updatedAt <= staleTimeMs
}

export const clearApiQueryCache = (): void => {
    queryCacheEpoch += 1
    queryCache.clear()
    inFlightRequests.clear()
    queryCacheRevisions.clear()
}

export const invalidateApiQueryCache = (...keys: ApiCacheKey[]): void => {
    if (keys.length === 0) {
        clearApiQueryCache()
        return
    }

    keys.forEach((key) => invalidateQueryCacheKey(toCacheKey(key)))
}

/** Reads the raw cached `queryFn` result for a key, before any `select` projection. */
export const getApiQueryCacheData = <TData>(key: ApiCacheKey): TData | undefined => {
    if (isServerRendering()) {
        return undefined
    }

    const cacheEntry = queryCache.get(toCacheKey(key))
    return cacheEntry?.data as TData | undefined
}

/**
 * Writes the raw cached result for a key, in the shape `queryFn` returns. Returning
 * `undefined` from the updater evicts the entry. Used for optimistic updates.
 */
export const setApiQueryCacheData = <TData>(
    key: ApiCacheKey,
    valueOrUpdater: TData | ((current: TData | undefined) => TData | undefined)
): TData | undefined => {
    if (isServerRendering()) {
        return undefined
    }

    const serializedKey = toCacheKey(key)
    const currentValue = queryCache.get(serializedKey)?.data as TData | undefined
    const nextValue =
        typeof valueOrUpdater === 'function' ? (valueOrUpdater as (current: TData | undefined) => TData | undefined)(currentValue) : valueOrUpdater

    invalidateQueryCacheKey(serializedKey)

    if (nextValue === undefined) {
        return undefined
    }

    queryCache.set(serializedKey, {
        data: nextValue,
        updatedAt: Date.now()
    })

    return nextValue
}

function createApiQuery<TData, TSelected, TError>(options: UseSelectedApiQueryOptions<TData, TSelected, TError>) {
    const data = ref<TSelected | undefined>(options.initialData)
    const error = ref<TError | null>(null)
    // A disabled query has no fetch pending, so it must not start out loading;
    // otherwise a consumer gated behind `enabled` renders a spinner forever.
    const isLoading = ref(options.initialData === undefined && resolveEnabled(options.enabled))
    const isFetching = ref(false)
    const activeExecutionIds = new Set<number>()
    let latestExecutionId = 0
    let latestExecutionCacheKey: string | undefined

    const staleTimeMs = options.staleTimeMs ?? DEFAULT_STALE_TIME
    const retryCount = options.retry ?? DEFAULT_RETRY_COUNT
    const retryDelayMs = options.retryDelayMs ?? DEFAULT_RETRY_DELAY_MS
    const resolveCacheKey = (): string => toCacheKey(toValue(options.key))
    const syncFetchState = (): void => {
        isFetching.value = activeExecutionIds.has(latestExecutionId)
        isLoading.value = data.value === undefined && isFetching.value
    }

    const fetchWithRetry = async (): Promise<TData> => {
        let attempt = 0

        while (true) {
            try {
                return await options.queryFn()
            } catch (caughtError) {
                if (attempt >= retryCount) {
                    throw caughtError
                }

                attempt += 1
                await wait(retryDelayMs)
            }
        }
    }

    const execute = async ({ force = false }: { force?: boolean } = {}): Promise<TSelected | undefined> => {
        const executionId = ++latestExecutionId

        // Never fetch or cache while the server renders. Page data already arrives
        // through Inertia props, and a request issued here would both run without a
        // request context and write another visitor's payload into `queryCache`.
        if (isServerRendering()) {
            syncFetchState()
            return data.value
        }

        if (!force && !resolveEnabled(options.enabled)) {
            syncFetchState()
            return data.value
        }

        const cacheKey = resolveCacheKey()
        latestExecutionCacheKey = cacheKey
        const cachedValue = queryCache.get(cacheKey)

        if (!force && cachedValue !== undefined && isCacheFresh(cachedValue.updatedAt, staleTimeMs)) {
            data.value = options.select(cachedValue.data as TData)
            error.value = null
            syncFetchState()
            return data.value
        }

        const requestRevision = currentQueryCacheRevision(cacheKey)
        activeExecutionIds.add(executionId)
        syncFetchState()

        try {
            // Deduped even when forced: a request already in flight is by definition
            // as fresh as one started now, so a refresh joins it rather than doubling
            // the load on the endpoint.
            const rawData = await dedupeRequest(cacheKey, fetchWithRetry)

            if (!isCurrentQueryCacheRevision(cacheKey, requestRevision)) {
                return data.value
            }

            queryCache.set(cacheKey, {
                data: rawData,
                updatedAt: Date.now()
            })

            const selectedData = options.select(rawData)

            if (executionId !== latestExecutionId || cacheKey !== resolveCacheKey()) {
                return selectedData
            }

            data.value = selectedData
            error.value = null
            return data.value
        } catch (caughtError) {
            if (!isCurrentQueryCacheRevision(cacheKey, requestRevision)) {
                return data.value
            }

            const mappedError = mapErrorWith(options.mapError, caughtError)

            if (executionId === latestExecutionId && cacheKey === resolveCacheKey()) {
                error.value = mappedError
            }

            throw mappedError
        } finally {
            activeExecutionIds.delete(executionId)
            syncFetchState()
        }
    }

    const refresh = async (): Promise<TSelected | undefined> => {
        return execute({ force: true })
    }

    watch(
        () => [resolveEnabled(options.enabled), resolveCacheKey()] as const,
        ([enabled, cacheKey], previousState) => {
            if (!enabled) {
                const wasEnabled = previousState?.[0] === true
                const hasActiveExecutionForPreviousKey = activeExecutionIds.has(latestExecutionId) && latestExecutionCacheKey !== cacheKey

                if (wasEnabled || hasActiveExecutionForPreviousKey) {
                    latestExecutionId += 1
                    latestExecutionCacheKey = undefined
                }

                syncFetchState()
                return
            }

            // The failure is already captured in `error`, so swallowing it here keeps
            // a background fetch from surfacing as an unhandled promise rejection.
            // `refresh()` still rejects for callers that want to await the outcome.
            void execute().catch(() => undefined)
        },
        { immediate: true }
    )

    const isError = computed(() => error.value !== null)
    // Requires data: a disabled query is neither loading nor errored, but it has
    // resolved nothing, so reporting success would be a lie.
    const isSuccess = computed(() => !isLoading.value && !isError.value && data.value !== undefined)

    return {
        data: readonly(data),
        error: readonly(error),
        isLoading: readonly(isLoading),
        isFetching: readonly(isFetching),
        isError,
        isSuccess,
        refresh
    }
}

export function useApiQuery<TData, TSelected, TError = ApiError>(
    options: UseSelectedApiQueryOptions<TData, TSelected, TError>
): ReturnType<typeof createApiQuery<TData, TSelected, TError>>
export function useApiQuery<TData, TSelected = TData, TError = ApiError>(
    options: IsSameType<TData, TSelected> extends true ? UseIdentityApiQueryOptions<TData, TError> : never
): ReturnType<typeof createApiQuery<TData, TData, TError>>
export function useApiQuery<TData, TSelected, TError>(
    options: UseSelectedApiQueryOptions<TData, TSelected, TError> | UseIdentityApiQueryOptions<TData, TError>
): ReturnType<typeof createApiQuery<TData, TSelected, TError>> | ReturnType<typeof createApiQuery<TData, TData, TError>> {
    if (options.select === undefined) {
        return createApiQuery({
            ...options,
            select: (rawData: TData): TData => rawData
        })
    }

    return createApiQuery(options)
}

export function useApiMutation<TVariables, TResult, TError = ApiError, TContext = unknown>(
    options: UseApiMutationOptions<TVariables, TResult, TError, TContext>
) {
    const data = ref<TResult | undefined>()
    const error = ref<TError | null>(null)
    const isPending = ref(false)

    const mutate = async (variables: TVariables): Promise<TResult> => {
        isPending.value = true
        error.value = null

        let context: TContext | undefined

        try {
            context = (await options.onMutate?.(variables)) as TContext | undefined
            const result = await options.mutationFn(variables)

            data.value = result
            await options.onSuccess?.(result, variables, context)

            if (options.invalidateKeys !== undefined && options.invalidateKeys.length > 0) {
                invalidateApiQueryCache(...options.invalidateKeys)
            }

            await options.onSettled?.(result, null, variables, context)
            return result
        } catch (caughtError) {
            const mappedError = mapErrorWith(options.mapError, caughtError)
            error.value = mappedError

            await options.onError?.(mappedError, variables, context)
            await options.onSettled?.(undefined, mappedError, variables, context)
            throw mappedError
        } finally {
            isPending.value = false
        }
    }

    const reset = (): void => {
        data.value = undefined
        error.value = null
        isPending.value = false
    }

    return {
        data: readonly(data),
        error: readonly(error),
        isPending: readonly(isPending),
        mutate,
        reset
    }
}
