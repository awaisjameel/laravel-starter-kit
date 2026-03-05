import { afterEach, describe, expect, it, vi } from 'vitest'
import { apiRequest } from '../useApiClient'

const isObjectRecord = (value: unknown): value is Record<string, unknown> => typeof value === 'object' && value !== null

const toJsonResponse = (payload: unknown, status = 200): Response => {
    return new Response(JSON.stringify(payload), {
        status,
        headers: {
            'Content-Type': 'application/json'
        }
    })
}

const toNoContentResponse = (): Response => {
    return new Response(null, {
        status: 204
    })
}

describe('apiRequest', () => {
    afterEach(() => {
        vi.restoreAllMocks()
    })

    it('returns unknown payload when no parser is provided', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => toJsonResponse({ ok: true }))
        )

        const response = await apiRequest({
            url: '/api/v1/ping'
        })

        expect(response).toEqual({ ok: true })
    })

    it('uses parseResponse to return validated data', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => toJsonResponse({ id: 9, email: 'user@example.com' }))
        )

        const response = await apiRequest({
            url: '/api/v1/users/9',
            parseResponse: (payload) => {
                if (!isObjectRecord(payload) || typeof payload.id !== 'number' || typeof payload.email !== 'string') {
                    throw new Error('Invalid user payload')
                }

                return {
                    id: payload.id,
                    email: payload.email
                }
            }
        })

        expect(response).toEqual({ id: 9, email: 'user@example.com' })
    })

    it('throws a typed error when parseResponse rejects payload', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => toJsonResponse({ id: 'not-a-number' }))
        )

        await expect(
            apiRequest({
                url: '/api/v1/users/invalid',
                parseResponse: () => {
                    throw new Error('Invalid user payload')
                }
            })
        ).rejects.toEqual({
            message: 'Invalid user payload',
            status: 200,
            code: 'invalid_response_payload'
        })
    })

    it('allows parseResponse to handle 204 responses explicitly', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(async () => toNoContentResponse())
        )

        const response = await apiRequest({
            url: '/api/v1/users/9',
            method: 'DELETE',
            parseResponse: (payload) => {
                if (payload !== undefined) {
                    throw new Error('Expected no content payload')
                }

                return true
            }
        })

        expect(response).toBe(true)
    })
})
