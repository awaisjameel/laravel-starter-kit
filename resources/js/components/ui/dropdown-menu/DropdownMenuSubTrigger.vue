<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { cn, omitUndefinedProps } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { reactiveOmit } from '@vueuse/core'
import {
  DropdownMenuSubTrigger,
  type DropdownMenuSubTriggerProps,
  useForwardProps,
} from 'reka-ui'

const props = defineProps<DropdownMenuSubTriggerProps & { class?: HTMLAttributes['class'], inset?: boolean }>()

const delegatedProps = reactiveOmit(props, 'class', 'inset')
const forwardedProps = useForwardProps(computed(() => omitUndefinedProps(delegatedProps)))
</script>

<template>
  <DropdownMenuSubTrigger
    data-slot="dropdown-menu-sub-trigger"
    v-bind="forwardedProps"
    :class="cn(
      appTheme.floating.item,
      appTheme.floating.subTrigger,
      props.class,
    )"
  >
    <slot />
    <IconLucideChevronRight class="ml-auto size-4" />
  </DropdownMenuSubTrigger>
</template>
