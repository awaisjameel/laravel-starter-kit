<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { ToastCloseProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { ToastClose, useForwardProps } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<
    ToastCloseProps & {
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
    <ToastClose
        data-slot="toast-close"
        v-bind="forwarded"
        :class="cn(appTheme.toast.close, props.class)"
    >
        <slot>
            <IconLucideX class="size-4" />
        </slot>
    </ToastClose>
</template>
