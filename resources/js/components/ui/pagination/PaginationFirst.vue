<script setup lang="ts">
import type { PaginationFirstProps } from "reka-ui"
import { computed, type HTMLAttributes } from "vue"
import type { ButtonVariants } from '@/components/ui/button'
import { reactiveOmit } from "@vueuse/core"
import { PaginationFirst, useForwardProps } from "reka-ui"
import { cn, omitUndefinedProps } from "@/lib/utils"
import { buttonStyles } from '@/components/ui/button'
import { appTheme } from '@/lib/theme'

const props = withDefaults(defineProps<PaginationFirstProps & {
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}>(), {
  size: "default",
})

const delegatedProps = reactiveOmit(props, "class", "size")
const forwarded = useForwardProps(computed(() => omitUndefinedProps(delegatedProps)))
</script>

<template>
  <PaginationFirst
    data-slot="pagination-first"
    :class="cn(buttonStyles({ variant: 'ghost', size }), appTheme.pagination.navigation, props.class)"
    v-bind="forwarded"
  >
    <slot>
      <IconLucideChevronsLeft />
      <span class="hidden sm:block">First</span>
    </slot>
  </PaginationFirst>
</template>
