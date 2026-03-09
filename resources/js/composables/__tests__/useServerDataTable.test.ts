import { describe, expect, it } from 'vitest'
import { resolveServerTableInitialQuery } from '../useServerDataTable'

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
