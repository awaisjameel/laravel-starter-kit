<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { RadioGroupItemProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { Circle } from 'lucide-vue-next'
import { RadioGroupIndicator, RadioGroupItem, useForwardProps } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<
    RadioGroupItemProps & {
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
    <RadioGroupItem
        data-slot="radio-group-item"
        v-bind="forwarded"
        :class="
            cn(
                'border-input text-primary focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aspect-square size-4 rounded-full border shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
    >
        <RadioGroupIndicator data-slot="radio-group-indicator" class="relative flex items-center justify-center">
            <Circle class="fill-primary absolute top-1/2 left-1/2 size-2 -translate-x-1/2 -translate-y-1/2" />
        </RadioGroupIndicator>
    </RadioGroupItem>
</template>
