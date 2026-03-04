export interface ApiError {
    message: string
    status?: number
    code?: string
    fieldErrors?: Record<string, string[]>
}

export type ApiRequestMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

export interface ApiRequestOptions {
    url: string
    method?: ApiRequestMethod
    query?: Record<string, string | number | boolean | null | undefined>
    body?: unknown
    headers?: Record<string, string>
    signal?: AbortSignal
}

const isObjectRecord = (value: unknown): value is Record<string, unknown> => typeof value === 'object' && value !== null
const isMutatingMethod = (method: ApiRequestMethod): boolean => method !== 'GET'

const resolveCsrfToken = (): string | undefined => {
    if (typeof document === 'undefined') {
        return undefined
    }

    const csrfMeta = document.querySelector('meta[name="csrf-token"]')
    const token = csrfMeta?.getAttribute('content')

    return token !== null && token !== undefined && token !== '' ? token : undefined
}

const toQueryString = (query: Record<string, string | number | boolean | null | undefined>): string => {
    const searchParams = new URLSearchParams()

    Object.entries(query).forEach(([key, value]) => {
        if (value === undefined || value === null) {
            return
        }

        searchParams.set(key, String(value))
    })

    const serialized = searchParams.toString()
    return serialized === '' ? '' : `?${serialized}`
}

const resolveFieldErrors = (value: unknown): Record<string, string[]> | undefined => {
    if (!isObjectRecord(value)) {
        return undefined
    }

    const fieldErrors: Record<string, string[]> = {}

    Object.entries(value).forEach(([field, fieldValue]) => {
        if (Array.isArray(fieldValue)) {
            const messages = fieldValue.filter((message): message is string => typeof message === 'string' && message.trim() !== '')

            if (messages.length > 0) {
                fieldErrors[field] = messages
            }
        }
    })

    return Object.keys(fieldErrors).length > 0 ? fieldErrors : undefined
}

const buildApiError = (payload: unknown, status?: number): ApiError => {
    if (isObjectRecord(payload)) {
        const message = typeof payload.message === 'string' && payload.message.trim() !== '' ? payload.message : 'Request failed.'

        return {
            message,
            status,
            code: typeof payload.code === 'string' ? payload.code : undefined,
            fieldErrors: resolveFieldErrors(payload.errors)
        }
    }

    if (typeof payload === 'string' && payload.trim() !== '') {
        return {
            message: payload,
            status
        }
    }

    return {
        message: 'Request failed.',
        status
    }
}

export const normalizeApiError = (error: unknown): ApiError => {
    if (isObjectRecord(error) && typeof error.message === 'string') {
        return {
            message: error.message,
            status: typeof error.status === 'number' ? error.status : undefined,
            code: typeof error.code === 'string' ? error.code : undefined,
            fieldErrors: resolveFieldErrors(error.fieldErrors)
        }
    }

    if (error instanceof Error) {
        return {
            message: error.message
        }
    }

    return {
        message: 'An unexpected error occurred.'
    }
}

export async function apiRequest<TResponse>(options: ApiRequestOptions): Promise<TResponse> {
    const requestUrl = `${options.url}${options.query !== undefined ? toQueryString(options.query) : ''}`
    const hasBody = options.body !== undefined
    const method = options.method ?? 'GET'
    const csrfToken = resolveCsrfToken()

    const response = await fetch(requestUrl, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            ...(hasBody ? { 'Content-Type': 'application/json' } : {}),
            ...(isMutatingMethod(method) && csrfToken !== undefined ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            ...options.headers
        },
        body: hasBody ? JSON.stringify(options.body) : undefined,
        signal: options.signal
    })

    if (response.status === 204) {
        return undefined as TResponse
    }

    const contentType = response.headers.get('content-type')
    const isJson = contentType !== null && contentType.includes('application/json')
    const payload = isJson ? ((await response.json()) as unknown) : ((await response.text()) as unknown)

    if (!response.ok) {
        throw buildApiError(payload, response.status)
    }

    return payload as TResponse
}
