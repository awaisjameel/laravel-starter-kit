<script setup lang="ts">
import { computed } from 'vue'
import { omitUndefinedProps } from '@/lib/utils'
import type { SelectSeparatorProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { SelectSeparator } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<SelectSeparatorProps & { class?: HTMLAttributes["class"] }>()

const delegated = reactiveOmit(props, "class")
const delegatedProps = computed(() => omitUndefinedProps(delegated))
</script>

<template>
  <SelectSeparator
    data-slot="select-separator"
    v-bind="delegatedProps"
    :class="cn('bg-border pointer-events-none -mx-1 my-1 h-px', props.class)"
  />
</template>
