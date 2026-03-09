<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { ToastCloseProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { X } from 'lucide-vue-next'
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
        :class="cn('absolute top-2 right-2 rounded-md p-1 text-foreground/70 transition-colors hover:text-foreground', props.class)"
    >
        <slot>
            <X class="size-4" />
        </slot>
    </ToastClose>
</template>
