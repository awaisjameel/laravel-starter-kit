<script setup lang="ts">
import { omitUndefinedProps } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import type { ToastTitleProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { ToastTitle, useForwardProps } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<
    ToastTitleProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = computed(() => {
    const { class: _class, ...delegated } = props
    return delegated
})

const rawForwardedProps = useForwardProps(delegatedProps)
const forwarded = computed(() => omitUndefinedProps(rawForwardedProps.value))
</script>

<template>
    <ToastTitle data-slot="toast-title" v-bind="forwarded" :class="cn('text-sm font-semibold', props.class)">
        <slot />
    </ToastTitle>
</template>
