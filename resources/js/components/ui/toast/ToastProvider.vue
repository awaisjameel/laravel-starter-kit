<script setup lang="ts">
import { computed } from 'vue'
import type { ToastProviderProps } from 'reka-ui'
import { ToastProvider, useForwardProps } from 'reka-ui'
import { omitUndefinedProps } from '@/lib/utils'

const props = defineProps<ToastProviderProps>()
const rawForwardedProps = useForwardProps(props)
const forwarded = computed(() => omitUndefinedProps(rawForwardedProps.value))
</script>

<template>
    <ToastProvider data-slot="toast-provider" v-bind="forwarded">
        <slot />
    </ToastProvider>
</template>
