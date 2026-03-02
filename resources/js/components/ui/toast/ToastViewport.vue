<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { ToastViewportProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { ToastViewport, useForwardProps } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<
    ToastViewportProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = computed(() => {
    const { class: _class, ...delegated } = props
    return delegated
})

const forwarded = useForwardProps(delegatedProps)
</script>

<template>
    <ToastViewport
        data-slot="toast-viewport"
        v-bind="forwarded"
        :class="cn('fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse gap-2 p-4 sm:top-auto sm:right-0 sm:bottom-0 sm:flex-col md:max-w-[420px]', props.class)"
    />
</template>
