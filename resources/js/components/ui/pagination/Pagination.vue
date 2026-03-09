<script setup lang="ts">
import type { PaginationRootEmits, PaginationRootProps } from "reka-ui"
import { computed, type HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { PaginationRoot, useForwardPropsEmits } from "reka-ui"
import { cn, omitUndefinedProps } from "@/lib/utils"

const props = defineProps<PaginationRootProps & {
  class?: HTMLAttributes["class"]
}>()
const emits = defineEmits<PaginationRootEmits>()

const delegatedProps = reactiveOmit(props, "class", "itemsPerPage")
const forwarded = useForwardPropsEmits(computed(() => omitUndefinedProps(delegatedProps)), emits)
</script>

<template>
  <PaginationRoot
    v-slot="slotProps"
    data-slot="pagination"
    :items-per-page="props.itemsPerPage"
    v-bind="forwarded"
    :class="cn('mx-auto flex w-full justify-center', props.class)"
  >
    <slot v-bind="slotProps" />
  </PaginationRoot>
</template>
