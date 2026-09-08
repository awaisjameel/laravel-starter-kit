import { router } from '@inertiajs/vue3'
import { describe, expect, it, vi } from 'vitest'
import { effectScope, ref } from 'vue'
import { resolveServerTableInitialQuery, useServerDataTable } from '../useServerDataTable'

it('synchronizes pagination after a preserved-state mutation redirect without issuing another visit', () => {
    const visit = vi.spyOn(router, 'get').mockImplementation(() => undefined)
    const pagination = ref({ current_page: 2, per_page: 5 })
    const scope = effectScope()

    try {
        scope.run(() => {
            const table = useServerDataTable({
                endpoint: ({ query } = {}) => ({ url: `/records?page=${query?.page}&perPage=${query?.perPage}`, method: 'get' }),
                initialQuery: { page: 2, perPage: 5 },
                pagination: () => pagination.value
            })

            pagination.value = { current_page: 1, per_page: 15 }
            expect(table.query.value).toEqual({ page: 1, perPage: 15 })
            expect(visit).not.toHaveBeenCalled()

            table.setPage(2)
            expect(visit).toHaveBeenCalledExactlyOnceWith(
                '/records?page=2&perPage=15',
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true
                }
            )
        })
    } finally {
        scope.stop()
        visit.mockRestore()
    }
})

type SortColumn = 'name' | 'email' | 'role' | 'created_at'

describe('resolveServerTableInitialQuery', () => {
    const fallback = {
        page: 2,
        perPage: 25,
        sortBy: 'created_at' as SortColumn,
        sortDirection: 'desc' as const
    }

    it('returns fallback values when location search is empty', () => {
        const result = resolveServerTableInitialQuery<SortColumn>({
            locationSearch: '',
            fallback,
            allowedSortBy: ['name', 'email', 'role', 'created_at']
        })

        expect(result).toEqual(fallback)
    })

    it('parses valid query values from location search', () => {
        const result = resolveServerTableInitialQuery<SortColumn>({
            locationSearch: '?page=3&perPage=50&search=alice&sortBy=name&sortDirection=asc',
            fallback,
            allowedSortBy: ['name', 'email', 'role', 'created_at']
        })

        expect(result).toEqual({
            page: 3,
            perPage: 50,
            search: 'alice',
            sortBy: 'name',
            sortDirection: 'asc'
        })
    })

    it('falls back for invalid or disallowed query values', () => {
        const result = resolveServerTableInitialQuery<SortColumn>({
            locationSearch: '?page=0&perPage=-1&search=   &sortBy=id&sortDirection=down',
            fallback,
            allowedSortBy: ['name', 'email', 'role', 'created_at'],
            defaultSortBy: 'created_at',
            defaultSortDirection: 'desc'
        })

        expect(result).toEqual({
            page: 2,
            perPage: 25,
            sortBy: 'created_at',
            sortDirection: 'desc'
        })
    })
})
