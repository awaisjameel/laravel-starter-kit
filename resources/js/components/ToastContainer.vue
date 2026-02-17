<script setup lang="ts">
    import { AlertTriangle, CheckCircle, Info, X, XCircle } from 'lucide-vue-next'

    const { toasts, remove } = useToast()

    const iconMap = {
        success: CheckCircle,
        error: XCircle,
        info: Info,
        warning: AlertTriangle
    }

    const colorMap = {
        success: 'bg-green-100 border-green-500 text-green-800 dark:bg-green-900/50 dark:border-green-400 dark:text-green-200',
        error: 'bg-red-100 border-red-500 text-red-800 dark:bg-red-900/50 dark:border-red-400 dark:text-red-200',
        info: 'bg-blue-100 border-blue-500 text-blue-800 dark:bg-blue-900/50 dark:border-blue-400 dark:text-blue-200',
        warning: 'bg-yellow-100 border-yellow-500 text-yellow-800 dark:bg-yellow-900/50 dark:border-yellow-400 dark:text-yellow-200'
    }

    const iconColorMap = {
        success: 'text-green-500 dark:text-green-400',
        error: 'text-red-500 dark:text-red-400',
        info: 'text-blue-500 dark:text-blue-400',
        warning: 'text-yellow-500 dark:text-yellow-400'
    }
</script>

<template>
    <Teleport to="body">
        <div class="fixed right-4 bottom-4 z-50 flex flex-col gap-2">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="['flex items-center gap-3 rounded-lg border-l-4 px-4 py-3 shadow-lg', colorMap[toast.type]]"
                >
                    <component :is="iconMap[toast.type]" :class="['h-5 w-5 flex-shrink-0', iconColorMap[toast.type]]" />
                    <span class="text-sm font-medium">{{ toast.message }}</span>
                    <button @click="remove(toast.id)" class="ml-2 rounded-full p-1 hover:bg-black/10 dark:hover:bg-white/10">
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
    .toast-enter-active,
    .toast-leave-active {
        transition: all 0.3s ease;
    }

    .toast-enter-from {
        opacity: 0;
        transform: translateX(100%);
    }

    .toast-leave-to {
        opacity: 0;
        transform: translateX(100%);
    }
</style>
