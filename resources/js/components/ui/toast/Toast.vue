<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { ToastRootEmits, ToastRootProps } from 'reka-ui'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { ToastRoot, useForwardPropsEmits } from 'reka-ui'

const toastVariants = cva(
    'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-4 pr-8 shadow-lg transition-all',
    {
        variants: {
            variant: {
                default: 'border bg-background text-foreground',
                success: 'border-green-500/40 bg-green-500/10 text-foreground',
                error: 'border-destructive/40 bg-destructive/10 text-foreground',
                info: 'border-blue-500/40 bg-blue-500/10 text-foreground',
                warning: 'border-amber-500/40 bg-amber-500/10 text-foreground',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
)

interface Props extends ToastRootProps {
    class?: HTMLAttributes['class']
    variant?: VariantProps<typeof toastVariants>['variant']
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
})

const emits = defineEmits<ToastRootEmits>()

const delegatedProps = reactiveOmit(props, 'class', 'variant')
const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <ToastRoot data-slot="toast" v-bind="forwarded" :class="cn(toastVariants({ variant: props.variant }), props.class)">
        <slot />
    </ToastRoot>
</template>
