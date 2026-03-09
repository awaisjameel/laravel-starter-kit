<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { ToastDescriptionProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { ToastDescription, useForwardProps } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<
    ToastDescriptionProps & {
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
    <ToastDescription data-slot="toast-description" v-bind="forwarded" :class="cn('text-sm opacity-90', props.class)">
        <slot />
    </ToastDescription>
</template>
