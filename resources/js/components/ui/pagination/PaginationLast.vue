<script setup lang="ts">
import type { PaginationLastProps } from "reka-ui"
import { computed, type HTMLAttributes } from "vue"
import type { ButtonVariants } from '@/components/ui/button'
import { reactiveOmit } from "@vueuse/core"
import { PaginationLast, useForwardProps } from "reka-ui"
import { cn, omitUndefinedProps } from "@/lib/utils"
import { buttonStyles } from '@/components/ui/button'
import { appTheme } from '@/lib/theme'

const props = withDefaults(defineProps<PaginationLastProps & {
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
}>(), {
  size: "default",
})

const delegatedProps = reactiveOmit(props, "class", "size")
const forwarded = useForwardProps(computed(() => omitUndefinedProps(delegatedProps)))
</script>

<template>
  <PaginationLast
    data-slot="pagination-last"
    :class="cn(buttonStyles({ variant: 'ghost', size }), appTheme.pagination.navigation, props.class)"
    v-bind="forwarded"
  >
    <slot>
      <span class="hidden sm:block">Last</span>
      <IconLucideChevronsRight />
    </slot>
  </PaginationLast>
</template>
