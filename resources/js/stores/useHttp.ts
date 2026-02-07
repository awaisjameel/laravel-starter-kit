import { defineStore } from 'pinia'

type QueryValue = string | number | boolean | null | undefined
type QueryParams = Record<string, QueryValue>

function buildQueryString(params: QueryParams = {}): string {
    const searchParams = new URLSearchParams()

    for (const [key, value] of Object.entries(params)) {
        if (value === null || value === undefined) {
            continue
        }

        searchParams.set(key, String(value))
    }

    const query = searchParams.toString()

    return query.length > 0 ? `?${query}` : ''
}

export const useHttp = defineStore('useHttp', () => {
    const appUrl = import.meta.env.VITE_APP_URL as string | undefined
    const baseUrl = `${appUrl ?? window.location.origin}/api`

    async function get<TResponse>(url: string, params: QueryParams = {}): Promise<TResponse> {
        const response = await fetch(`${baseUrl}${url}${buildQueryString(params)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })

        return (await response.json()) as TResponse
    }

    async function post<TResponse, TPayload extends Record<string, unknown>>(url: string, data: TPayload): Promise<TResponse> {
        const response = await fetch(`${baseUrl}${url}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })

        return (await response.json()) as TResponse
    }

    return { get, post }
})
