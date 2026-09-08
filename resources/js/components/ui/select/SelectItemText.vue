<script setup lang="ts">
import { omitUndefinedProps } from '@/lib/utils'
import type { SelectItemTextProps } from "reka-ui"
import { SelectItemText } from "reka-ui"
import { computed } from 'vue'

const props = defineProps<SelectItemTextProps>()

const forwardedProps = computed(() => omitUndefinedProps(props))
</script>

<template>
  <SelectItemText
    data-slot="select-item-text"
    v-bind="forwardedProps"
  >
    <slot />
  </SelectItemText>
</template>
