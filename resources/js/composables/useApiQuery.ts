import type { ComputedRef, Ref } from 'vue'
import type { ApiError } from './useApiClient'
import { normalizeApiError } from './useApiClient'

export type ApiCacheKey = string | string[]
type ApiCacheKeyInput = ApiCacheKey | Ref<ApiCacheKey> | ComputedRef<ApiCacheKey> | (() => ApiCacheKey)

type QueryCacheEntry<TData> = {
    data: TData
    updatedAt: number
}

type ErrorMapper<TError> = (error: unknown) => TError

interface UseApiQueryOptions<TData, TSelected = TData, TError = ApiError> {
    key: ApiCacheKeyInput
    queryFn: () => Promise<TData>
    enabled?: boolean | Ref<boolean> | ComputedRef<boolean> | (() => boolean)
    staleTimeMs?: number
    retry?: number
    retryDelayMs?: number
    select?: (data: TData) => TSelected
    initialData?: TSelected
    mapError?: ErrorMapper<TError>
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

const queryCache = new Map<string, QueryCacheEntry<unknown>>()

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

const resolveEnabled = (enabled: UseApiQueryOptions<unknown>['enabled']): boolean => {
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
    queryCache.clear()
}

export const invalidateApiQueryCache = (...keys: ApiCacheKey[]): void => {
    if (keys.length === 0) {
        queryCache.clear()
        return
    }

    keys.forEach((key) => {
        queryCache.delete(toCacheKey(key))
    })
}

export const getApiQueryCacheData = <TData>(key: ApiCacheKey): TData | undefined => {
    const cacheEntry = queryCache.get(toCacheKey(key))
    return cacheEntry?.data as TData | undefined
}

export const setApiQueryCacheData = <TData>(
    key: ApiCacheKey,
    valueOrUpdater: TData | ((current: TData | undefined) => TData | undefined)
): TData | undefined => {
    const serializedKey = toCacheKey(key)
    const currentValue = queryCache.get(serializedKey)?.data as TData | undefined
    const nextValue =
        typeof valueOrUpdater === 'function' ? (valueOrUpdater as (current: TData | undefined) => TData | undefined)(currentValue) : valueOrUpdater

    if (nextValue === undefined) {
        queryCache.delete(serializedKey)
        return undefined
    }

    queryCache.set(serializedKey, {
        data: nextValue,
        updatedAt: Date.now()
    })

    return nextValue
}

export function useApiQuery<TData, TSelected = TData, TError = ApiError>(options: UseApiQueryOptions<TData, TSelected, TError>) {
    const data = ref<TSelected | undefined>(options.initialData)
    const error = ref<TError | null>(null)
    const isLoading = ref(options.initialData === undefined)
    const isFetching = ref(false)

    const staleTimeMs = options.staleTimeMs ?? DEFAULT_STALE_TIME
    const retryCount = options.retry ?? DEFAULT_RETRY_COUNT
    const retryDelayMs = options.retryDelayMs ?? DEFAULT_RETRY_DELAY_MS
    const resolveCacheKey = (): string => toCacheKey(toValue(options.key))

    const execute = async ({ force = false }: { force?: boolean } = {}): Promise<TSelected | undefined> => {
        const cacheKey = resolveCacheKey()

        if (!force && !resolveEnabled(options.enabled)) {
            return data.value
        }

        const cachedValue = queryCache.get(cacheKey)

        if (!force && cachedValue !== undefined && isCacheFresh(cachedValue.updatedAt, staleTimeMs)) {
            data.value = cachedValue.data as TSelected
            error.value = null
            isLoading.value = false
            return data.value
        }

        isFetching.value = true

        if (data.value === undefined) {
            isLoading.value = true
        }

        try {
            let attempt = 0

            while (attempt <= retryCount) {
                try {
                    const rawData = await options.queryFn()
                    const selectedData = options.select !== undefined ? options.select(rawData) : (rawData as unknown as TSelected)

                    queryCache.set(cacheKey, {
                        data: selectedData,
                        updatedAt: Date.now()
                    })

                    data.value = selectedData
                    error.value = null
                    isLoading.value = false
                    return selectedData
                } catch (caughtError) {
                    if (attempt >= retryCount) {
                        const mappedError = mapErrorWith(options.mapError, caughtError)
                        error.value = mappedError
                        isLoading.value = false
                        throw mappedError
                    }

                    attempt += 1
                    await wait(retryDelayMs)
                }
            }

            return data.value
        } finally {
            isFetching.value = false
        }
    }

    const refresh = async (): Promise<TSelected | undefined> => {
        return execute({ force: true })
    }

    watch(
        () => [resolveEnabled(options.enabled), resolveCacheKey()] as const,
        ([enabled]) => {
            if (enabled) {
                void execute()
            }
        },
        { immediate: true }
    )

    const isError = computed(() => error.value !== null)
    const isSuccess = computed(() => !isLoading.value && !isError.value)

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
