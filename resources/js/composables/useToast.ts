import { readonly, ref } from 'vue'

export interface Toast {
    id: number
    message: string
    type: 'success' | 'error' | 'info' | 'warning'
    duration?: number
}

const toasts = ref<Toast[]>([])
let toastId = 0

/**
 * Composable for managing toast notifications.
 * Provides a centralized way to show temporary feedback messages.
 */
export function useToast() {
    const show = (message: string, type: Toast['type'] = 'success', duration = 3000): void => {
        const id = ++toastId
        toasts.value.push({ id, message, type, duration })

        if (duration > 0) {
            setTimeout(() => {
                remove(id)
            }, duration)
        }
    }

    const remove = (id: number): void => {
        const index = toasts.value.findIndex((t) => t.id === id)
        if (index !== -1) {
            toasts.value.splice(index, 1)
        }
    }

    const success = (message: string, duration?: number): void => {
        show(message, 'success', duration)
    }

    const error = (message: string, duration?: number): void => {
        show(message, 'error', duration)
    }

    const info = (message: string, duration?: number): void => {
        show(message, 'info', duration)
    }

    const warning = (message: string, duration?: number): void => {
        show(message, 'warning', duration)
    }

    const clear = (): void => {
        toasts.value = []
    }

    return {
        toasts: readonly(toasts),
        show,
        remove,
        success,
        error,
        info,
        warning,
        clear
    }
}
