<script setup lang="ts">
    export interface LoadingOverlayProps {
        loading?: boolean
        text?: string
        fullscreen?: boolean
    }

    withDefaults(defineProps<LoadingOverlayProps>(), {
        loading: true,
        text: 'Loading...',
        fullscreen: false
    })
</script>

<template>
    <Transition name="fade">
        <div
            v-if="loading"
            :class="[
                'flex flex-col items-center justify-center gap-3 bg-white/80 backdrop-blur-sm dark:bg-gray-900/80',
                fullscreen ? 'fixed inset-0 z-50' : 'absolute inset-0 rounded-lg'
            ]"
            role="status"
            aria-live="polite"
        >
            <LoadingSpinner size="lg" />
            <span v-if="text" class="text-sm font-medium text-gray-600 dark:text-gray-400">
                {{ text }}
            </span>
        </div>
    </Transition>
</template>

<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.2s ease;
    }

    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>
