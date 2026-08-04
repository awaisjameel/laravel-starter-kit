<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { cn, omitUndefinedProps } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { reactiveOmit } from '@vueuse/core'
import { DropdownMenuItem, type DropdownMenuItemProps, useForwardProps } from 'reka-ui'

const props = withDefaults(defineProps<DropdownMenuItemProps & {
  class?: HTMLAttributes['class']
  inset?: boolean
  variant?: 'default' | 'destructive'
}>(), {
  variant: 'default',
})

const delegatedProps = reactiveOmit(props, 'inset', 'variant')

const forwardedProps = useForwardProps(computed(() => omitUndefinedProps(delegatedProps)))
</script>

<template>
  <DropdownMenuItem
    data-slot="dropdown-menu-item"
    :data-inset="inset ? '' : undefined"
    :data-variant="variant"
    v-bind="forwardedProps"
    :class="cn(appTheme.floating.item, variant === 'destructive' && appTheme.floating.destructive, props.class)"
  >
    <slot />
  </DropdownMenuItem>
</template>
