import type { ServerTableQuery, SortDirection } from '@/types/base-ui'
import type { QueryParams, RouteDefinition } from '@/wayfinder'
import { useDebounceFn } from '@vueuse/core'

interface ServerDataTableOptions<TSort extends string> {
    endpoint: (options?: { query?: QueryParams }) => RouteDefinition<'get'>
    initialQuery: ServerTableQuery<TSort>
    debounceMs?: number
}

const DEFAULT_DEBOUNCE = 300

const sanitizeQuery = <TSort extends string>(query: ServerTableQuery<TSort>): QueryParams => {
    const sanitized: QueryParams = {
        page: query.page,
        perPage: query.perPage
    }

    if (query.search !== undefined && query.search.trim() !== '') {
        sanitized.search = query.search.trim()
    }

    if (query.sortBy !== undefined && query.sortBy !== '') {
        sanitized.sortBy = query.sortBy
    }

    if (query.sortDirection !== undefined) {
        sanitized.sortDirection = query.sortDirection
    }

    return sanitized
}

export function useServerDataTable<TSort extends string>(options: ServerDataTableOptions<TSort>) {
    const query = ref<ServerTableQuery<TSort>>({
        page: options.initialQuery.page,
        perPage: options.initialQuery.perPage,
        search: options.initialQuery.search,
        sortBy: options.initialQuery.sortBy as TSort | undefined,
        sortDirection: options.initialQuery.sortDirection
    })

    const searchValue = ref(query.value.search ?? '')

    const visit = () => {
        const definition = options.endpoint({
            query: sanitizeQuery(query.value)
        })

        router.get(
            definition.url,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true
            }
        )
    }

    const debouncedSearch = useDebounceFn(() => {
        query.value.page = 1
        query.value.search = searchValue.value
        visit()
    }, options.debounceMs ?? DEFAULT_DEBOUNCE)

    const setPage = (page: number) => {
        if (page < 1 || page === query.value.page) {
            return
        }

        query.value.page = page
        visit()
    }

    const setPerPage = (perPage: number) => {
        if (perPage < 1 || perPage === query.value.perPage) {
            return
        }

        query.value.perPage = perPage
        query.value.page = 1
        visit()
    }

    const setSort = (sortBy: TSort) => {
        if (query.value.sortBy === sortBy) {
            query.value.sortDirection = query.value.sortDirection === 'asc' ? 'desc' : 'asc'
        } else {
            query.value.sortBy = sortBy as never
            query.value.sortDirection = 'asc'
        }

        query.value.page = 1
        visit()
    }

    const setSortDirection = (direction: SortDirection) => {
        query.value.sortDirection = direction
        query.value.page = 1
        visit()
    }

    watch(searchValue, () => {
        debouncedSearch()
    })

    return {
        query: readonly(query),
        searchValue,
        setPage,
        setPerPage,
        setSort,
        setSortDirection,
        refresh: visit
    }
}
