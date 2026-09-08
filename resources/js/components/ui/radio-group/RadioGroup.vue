<script setup lang="ts">
import { computed } from 'vue'
import { omitUndefinedProps } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import type { RadioGroupRootEmits, RadioGroupRootProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import { RadioGroupRoot, useForwardPropsEmits } from 'reka-ui'

const props = defineProps<
    RadioGroupRootProps & {
        class?: HTMLAttributes['class']
    }
>()

const emits = defineEmits<RadioGroupRootEmits>()

const delegatedProps = reactiveOmit(props, 'class')
const rawForwardedProps = useForwardPropsEmits(delegatedProps, emits)
const forwarded = computed(() => omitUndefinedProps(rawForwardedProps.value))
</script>

<template>
    <RadioGroupRoot data-slot="radio-group" v-bind="forwarded" :class="cn('grid gap-2', props.class)">
        <slot />
    </RadioGroupRoot>
</template>
