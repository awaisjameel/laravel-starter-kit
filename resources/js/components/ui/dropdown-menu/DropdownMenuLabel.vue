<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { cn, omitUndefinedProps } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { reactiveOmit } from '@vueuse/core'
import { DropdownMenuLabel, type DropdownMenuLabelProps, useForwardProps } from 'reka-ui'

const props = defineProps<DropdownMenuLabelProps & { class?: HTMLAttributes['class'], inset?: boolean }>()

const delegatedProps = reactiveOmit(props, 'class', 'inset')
const forwardedProps = useForwardProps(computed(() => omitUndefinedProps(delegatedProps)))
</script>

<template>
  <DropdownMenuLabel
    data-slot="dropdown-menu-label"
    :data-inset="inset ? '' : undefined"
    v-bind="forwardedProps"
    :class="cn(appTheme.floating.label, props.class)"
  >
    <slot />
  </DropdownMenuLabel>
</template>
