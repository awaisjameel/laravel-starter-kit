import { readonly, ref } from 'vue'

export interface ConfirmOptions {
    title?: string
    description?: string
    confirmText?: string
    cancelText?: string
    variant?: 'danger' | 'warning' | 'info' | 'success'
}

interface ConfirmState extends ConfirmOptions {
    open: boolean
    resolve: ((value: boolean) => void) | null
}

const state = ref<ConfirmState>({
    open: false,
    title: 'Confirm Action',
    description: 'Are you sure you want to proceed?',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'warning',
    resolve: null
})

/**
 * Composable for programmatic confirmation dialogs.
 * Provides a Promise-based API for confirmations.
 *
 * @example
 * ```typescript
 * const { confirm } = useConfirm()
 *
 * const handleDelete = async () => {
 *   const confirmed = await confirm({
 *     title: 'Delete Item',
 *     description: 'This action cannot be undone.',
 *     variant: 'danger',
 *   })
 *
 *   if (confirmed) {
 *     // proceed with deletion
 *   }
 * }
 * ```
 */
export function useConfirm() {
    const confirm = (options: ConfirmOptions = {}): Promise<boolean> => {
        state.value = {
            ...state.value,
            ...options,
            open: true,
            resolve: null
        }

        return new Promise((resolve) => {
            state.value.resolve = resolve
        })
    }

    const handleConfirm = () => {
        state.value.resolve?.(true)
        state.value.open = false
    }

    const handleCancel = () => {
        state.value.resolve?.(false)
        state.value.open = false
    }

    return {
        state: readonly(state),
        confirm,
        handleConfirm,
        handleCancel
    }
}
