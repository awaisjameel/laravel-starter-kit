<script setup lang="ts">
import { computed } from 'vue'
import { omitUndefinedProps } from '@/lib/utils'
import type { PaginationListItemProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import type { ButtonVariants } from '@/components/ui/button'
import { reactiveOmit } from "@vueuse/core"
import { PaginationListItem } from "reka-ui"
import { cn } from "@/lib/utils"
import { buttonStyles } from '@/components/ui/button'

const props = withDefaults(defineProps<PaginationListItemProps & {
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
  isActive?: boolean
}>(), {
  size: "icon",
})

const delegated = reactiveOmit(props, "class", "size", "isActive", "value")
const delegatedProps = computed(() => omitUndefinedProps(delegated))
</script>

<template>
  <PaginationListItem
    data-slot="pagination-item"
    :value="props.value"
    v-bind="delegatedProps"
    :class="cn(
      buttonStyles({
        variant: isActive ? 'outline' : 'ghost',
        size,
      }),
      props.class)"
  >
    <slot />
  </PaginationListItem>
</template>
