<script setup lang="ts">
import type { PaginationPrevProps } from "reka-ui"
import { computed, type HTMLAttributes } from "vue"
import type { ButtonVariants } from '@/components/ui/button'
import { reactiveOmit } from "@vueuse/core"
import { PaginationPrev, useForwardProps } from "reka-ui"
import { cn, omitUndefinedProps } from "@/lib/utils"
import { buttonStyles } from '@/components/ui/button'
import { appTheme } from '@/lib/theme'

const props = withDefaults(defineProps<PaginationPrevProps & {
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}>(), {
  size: "default",
})

const delegatedProps = reactiveOmit(props, "class", "size")
const forwarded = useForwardProps(computed(() => omitUndefinedProps(delegatedProps)))
</script>

<template>
  <PaginationPrev
    data-slot="pagination-previous"
    :class="cn(buttonStyles({ variant: 'ghost', size }), appTheme.pagination.navigation, props.class)"
    v-bind="forwarded"
  >
    <slot>
      <IconLucideChevronLeft />
      <span class="hidden sm:block">Previous</span>
    </slot>
  </PaginationPrev>
</template>
