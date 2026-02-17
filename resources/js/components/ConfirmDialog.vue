<script setup lang="ts">
    import { AlertTriangle, CheckCircle, Info, Trash2 } from 'lucide-vue-next'

    export interface ConfirmDialogProps {
        open: boolean
        title?: string
        description?: string
        confirmText?: string
        cancelText?: string
        variant?: 'danger' | 'warning' | 'info' | 'success'
        loading?: boolean
    }

    withDefaults(defineProps<ConfirmDialogProps>(), {
        title: 'Confirm Action',
        description: 'Are you sure you want to proceed?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        variant: 'warning',
        loading: false
    })

    const emit = defineEmits<{
        (e: 'update:open', value: boolean): void
        (e: 'confirm'): void
        (e: 'cancel'): void
    }>()

    const iconMap = {
        danger: Trash2,
        warning: AlertTriangle,
        info: Info,
        success: CheckCircle
    }

    const iconColorMap = {
        danger: 'text-red-500 dark:text-red-400',
        warning: 'text-yellow-500 dark:text-yellow-400',
        info: 'text-blue-500 dark:text-blue-400',
        success: 'text-green-500 dark:text-green-400'
    }

    const buttonVariantMap = {
        danger: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        warning: 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
        info: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        success: 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
    }

    const handleConfirm = () => {
        emit('confirm')
    }

    const handleCancel = () => {
        emit('update:open', false)
        emit('cancel')
    }
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
                aria-describedby="modal-description"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="handleCancel" />

                <!-- Dialog -->
                <div
                    class="relative z-10 w-full max-w-md transform overflow-hidden rounded-xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800"
                >
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div
                            :class="[
                                'flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full',
                                variant === 'danger' && 'bg-red-100 dark:bg-red-900/50',
                                variant === 'warning' && 'bg-yellow-100 dark:bg-yellow-900/50',
                                variant === 'info' && 'bg-blue-100 dark:bg-blue-900/50',
                                variant === 'success' && 'bg-green-100 dark:bg-green-900/50'
                            ]"
                        >
                            <component :is="iconMap[variant]" :class="['h-6 w-6', iconColorMap[variant]]" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ title }}
                            </h3>
                            <p id="modal-description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            :disabled="loading"
                            @click="handleCancel"
                        >
                            {{ cancelText }}
                        </button>
                        <button
                            type="button"
                            :class="[
                                'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:outline-none disabled:opacity-50',
                                buttonVariantMap[variant]
                            ]"
                            :disabled="loading"
                            @click="handleConfirm"
                        >
                            <svg v-if="loading" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
    .modal-enter-active,
    .modal-leave-active {
        transition: all 0.2s ease;
    }

    .modal-enter-from,
    .modal-leave-to {
        opacity: 0;
    }

    .modal-enter-from .relative,
    .modal-leave-to .relative {
        transform: scale(0.95);
    }
</style>
