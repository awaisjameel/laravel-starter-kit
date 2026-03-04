import type { AppPageProps } from '@/types'
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest'
import { nextTick, reactive, ref, watch } from 'vue'
import { useFlashToasts } from '../useFlashToasts'

type ToastInput = {
    title: string
    description?: string
    duration?: number
}

type MockPage = {
    props: Pick<AppPageProps, 'flash'>
}

const success = vi.fn<(input: ToastInput) => void>()
const error = vi.fn<(input: ToastInput) => void>()
const info = vi.fn<(input: ToastInput) => void>()

let mockPage = createMockPage()

const usePageMock = vi.fn(() => mockPage)
const useToastMock = vi.fn(() => ({
    success,
    error,
    info
}))

const globalScope = globalThis as typeof globalThis & {
    ref: typeof ref
    watch: typeof watch
    usePage: () => MockPage
    useToast: () => {
        success: (input: ToastInput) => void
        error: (input: ToastInput) => void
        info: (input: ToastInput) => void
    }
}

beforeAll(() => {
    globalScope.ref = ref
    globalScope.watch = watch
    globalScope.usePage = usePageMock
    globalScope.useToast = useToastMock
})

beforeEach(() => {
    mockPage = createMockPage()

    usePageMock.mockClear()
    useToastMock.mockClear()
    usePageMock.mockImplementation(() => mockPage)

    success.mockReset()
    error.mockReset()
    info.mockReset()
})

describe('useFlashToasts', () => {
    it('emits toasts for message, error, and status flash values', async () => {
        useFlashToasts()
        await nextTick()

        expect(success).not.toHaveBeenCalled()
        expect(error).not.toHaveBeenCalled()
        expect(info).not.toHaveBeenCalled()

        mockPage.props.flash.message = 'Profile updated'
        mockPage.props.flash.error = 'Action failed'
        mockPage.props.flash.status = 'Verification sent'
        await nextTick()

        expect(success).toHaveBeenCalledWith({ title: 'Profile updated' })
        expect(error).toHaveBeenCalledWith({ title: 'Action failed' })
        expect(info).toHaveBeenCalledWith({ title: 'Verification sent' })
    })

    it('does not duplicate identical flash values', async () => {
        useFlashToasts()
        await nextTick()

        mockPage.props.flash.message = 'Saved'
        mockPage.props.flash.status = 'Updated'
        await nextTick()

        mockPage.props.flash.message = 'Saved'
        mockPage.props.flash.status = 'Updated'
        await nextTick()

        expect(success).toHaveBeenCalledTimes(1)
        expect(info).toHaveBeenCalledTimes(1)
    })
})

function createMockPage(): MockPage {
    return reactive({
        props: {
            flash: {
                message: undefined,
                error: undefined,
                status: undefined
            }
        }
    }) as MockPage
}
