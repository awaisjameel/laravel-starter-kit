import { beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick, ref } from 'vue'
import { clearApiQueryCache, getApiQueryCacheData, setApiQueryCacheData, useApiMutation, useApiQuery } from '../useApiQuery'

// A macrotask boundary drains every pending microtask, so an assertion can observe
// the end of a fetch chain without counting the individual `await` hops inside it.
const flushPromises = async (): Promise<void> => {
    await new Promise((resolve) => {
        setTimeout(resolve, 0)
    })
}

describe('useApiQuery', () => {
    beforeEach(() => {
        clearApiQueryCache()
    })

    it('reuses cached data for the same cache key within stale time', async () => {
        const queryFn = vi.fn(async () => ({ count: 1 }))

        const firstQuery = useApiQuery({
            key: ['users', 'summary'],
            queryFn,
            enabled: false,
            staleTimeMs: 60_000
        })

        await firstQuery.refresh()

        const secondQuery = useApiQuery({
            key: ['users', 'summary'],
            queryFn,
            staleTimeMs: 60_000
        })

        await nextTick()

        expect(queryFn).toHaveBeenCalledTimes(1)
        expect(secondQuery.data.value).toEqual({ count: 1 })
    })

    it('never fetches or caches while the server renders', async () => {
        vi.stubEnv('SSR', true)

        try {
            const queryFn = vi.fn(async () => ({ count: 1 }))

            const query = useApiQuery({
                key: 'ssr-query',
                queryFn
            })

            await nextTick()
            await query.refresh()

            expect(queryFn).not.toHaveBeenCalled()
            expect(query.isLoading.value).toBe(false)

            // The SSR process is shared by every visitor, so nothing may be written
            // to (or read back from) the module-level cache while it renders.
            setApiQueryCacheData<number[]>('ssr-query', [1])
            expect(getApiQueryCacheData<number[]>('ssr-query')).toBeUndefined()
        } finally {
            vi.unstubAllEnvs()
        }

        expect(getApiQueryCacheData<number[]>('ssr-query')).toBeUndefined()
    })

    it('shares one in-flight request between consumers of the same key', async () => {
        let resolveQuery: (value: { count: number }) => void = () => undefined
        const queryFn = vi.fn(
            async () =>
                await new Promise<{ count: number }>((resolve) => {
                    resolveQuery = resolve
                })
        )

        const firstQuery = useApiQuery({ key: 'shared-key', queryFn })
        // `select` stays per consumer, so joining the shared request must not leak
        // one consumer's projection into the other.
        const secondQuery = useApiQuery({ key: 'shared-key', queryFn, select: (value) => value.count })

        await nextTick()
        expect(queryFn).toHaveBeenCalledTimes(1)

        resolveQuery({ count: 7 })
        await Promise.all([firstQuery.refresh(), secondQuery.refresh()])

        expect(queryFn).toHaveBeenCalledTimes(1)
        expect(firstQuery.data.value).toEqual({ count: 7 })
        expect(secondQuery.data.value).toBe(7)
        expect(getApiQueryCacheData<{ count: number }>('shared-key')).toEqual({ count: 7 })
    })

    it('does not report loading for a disabled query without initial data', async () => {
        const enabled = ref(false)
        const queryFn = vi.fn(async () => ({ count: 1 }))

        const query = useApiQuery({ key: 'disabled-key', queryFn, enabled })

        expect(query.isLoading.value).toBe(false)
        expect(query.isSuccess.value).toBe(false)
        expect(queryFn).not.toHaveBeenCalled()

        enabled.value = true
        await nextTick()
        await flushPromises()

        expect(queryFn).toHaveBeenCalledTimes(1)
        expect(query.isLoading.value).toBe(false)
        expect(query.isSuccess.value).toBe(true)
        expect(query.data.value).toEqual({ count: 1 })
    })

    it('captures a failed background fetch in `error` without rejecting the watcher', async () => {
        const queryFn = vi.fn(async () => {
            throw new Error('network down')
        })

        const query = useApiQuery({ key: 'failing-key', queryFn, retry: 0 })

        await flushPromises()

        expect(query.isError.value).toBe(true)
        expect(query.isLoading.value).toBe(false)
        expect(query.isSuccess.value).toBe(false)
    })

    it('retries failed requests and resolves when a retry succeeds', async () => {
        const queryFn = vi
            .fn<() => Promise<{ ok: boolean }>>()
            .mockRejectedValueOnce(new Error('network 1'))
            .mockRejectedValueOnce(new Error('network 2'))
            .mockResolvedValue({ ok: true })

        const query = useApiQuery({
            key: 'retry-query',
            queryFn,
            enabled: false,
            retry: 2,
            retryDelayMs: 0
        })

        await expect(query.refresh()).resolves.toEqual({ ok: true })
        expect(queryFn).toHaveBeenCalledTimes(3)
        expect(query.error.value).toBeNull()
    })
})

describe('useApiMutation', () => {
    beforeEach(() => {
        clearApiQueryCache()
    })

    it('supports optimistic updates with rollback on error', async () => {
        setApiQueryCacheData<number[]>('users:list', [1])

        const mutation = useApiMutation<number, { id: number }, { message: string }, number[]>({
            mutationFn: vi.fn(async () => {
                throw new Error('Mutation failed')
            }),
            onMutate: async (value) => {
                const previousUsers = getApiQueryCacheData<number[]>('users:list') ?? []
                setApiQueryCacheData<number[]>('users:list', [...previousUsers, value])
                return previousUsers
            },
            onError: async (_error, _variables, context) => {
                setApiQueryCacheData('users:list', context)
            },
            mapError: (error) => ({
                message: error instanceof Error ? error.message : 'Unknown error'
            })
        })

        await expect(mutation.mutate(2)).rejects.toEqual({ message: 'Mutation failed' })

        expect(getApiQueryCacheData<number[]>('users:list')).toEqual([1])
    })
})
