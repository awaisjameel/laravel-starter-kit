<script setup lang="ts">
import type { SelectItemTextProps } from "reka-ui"
import { SelectItemText } from "reka-ui"
import { computed } from 'vue'

const props = defineProps<SelectItemTextProps>()

const forwardedProps = computed(() => props as Record<string, unknown>)
</script>

<template>
  <SelectItemText
    data-slot="select-item-text"
    v-bind="forwardedProps"
  >
    <slot />
  </SelectItemText>
</template>
