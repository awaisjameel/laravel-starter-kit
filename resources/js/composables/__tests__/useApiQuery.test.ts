import { beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick } from 'vue'
import { clearApiQueryCache, getApiQueryCacheData, setApiQueryCacheData, useApiMutation, useApiQuery } from '../useApiQuery'

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
