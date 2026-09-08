<script setup lang="ts">
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import {
  DropdownMenuSeparator,
  type DropdownMenuSeparatorProps,
} from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<DropdownMenuSeparatorProps & {
  class?: HTMLAttributes['class']
}>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated as Partial<DropdownMenuSeparatorProps>
})
</script>

<template>
  <DropdownMenuSeparator
    data-slot="dropdown-menu-separator"
    v-bind="delegatedProps"
    :class="cn(appTheme.floating.separator, props.class)"
  />
</template>
