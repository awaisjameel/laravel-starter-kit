<script setup lang="ts">
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import {
  DropdownMenuSubContent,
  type DropdownMenuSubContentEmits,
  type DropdownMenuSubContentProps,
  useForwardPropsEmits,
} from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<DropdownMenuSubContentProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<DropdownMenuSubContentEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated as Partial<DropdownMenuSubContentProps>
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <DropdownMenuSubContent
    data-slot="dropdown-menu-sub-content"
    v-bind="forwarded"
    :class="cn(appTheme.floating.content, 'origin-(--reka-dropdown-menu-content-transform-origin)', props.class)"
  >
    <slot />
  </DropdownMenuSubContent>
</template>
