<script setup lang="ts">
import type { ToastProviderProps } from 'reka-ui'
import { ToastProvider } from 'reka-ui'

defineProps<ToastProviderProps>()
</script>

<template>
    <ToastProvider data-slot="toast-provider" v-bind="$props">
        <slot />
    </ToastProvider>
</template>
