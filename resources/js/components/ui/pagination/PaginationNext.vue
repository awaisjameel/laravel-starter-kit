<script setup lang="ts">
import type { PaginationNextProps } from "reka-ui"
import { computed, type HTMLAttributes } from "vue"
import type { ButtonVariants } from '@/components/ui/button'
import { reactiveOmit } from "@vueuse/core"
import { PaginationNext, useForwardProps } from "reka-ui"
import { cn, omitUndefinedProps } from "@/lib/utils"
import { buttonStyles } from '@/components/ui/button'
import { appTheme } from '@/lib/theme'

const props = withDefaults(defineProps<PaginationNextProps & {
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}>(), {
  size: "default",
})

const delegatedProps = reactiveOmit(props, "class", "size")
const forwarded = useForwardProps(computed(() => omitUndefinedProps(delegatedProps)))
</script>

<template>
  <PaginationNext
    data-slot="pagination-next"
    :class="cn(buttonStyles({ variant: 'ghost', size }), appTheme.pagination.navigation, props.class)"
    v-bind="forwarded"
  >
    <slot>
      <span class="hidden sm:block">Next</span>
      <IconLucideChevronRight />
    </slot>
  </PaginationNext>
</template>
