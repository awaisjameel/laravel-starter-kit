<script setup lang="ts">
import { computed } from 'vue'
import { TooltipTrigger, type TooltipTriggerProps } from 'reka-ui'

const props = defineProps<TooltipTriggerProps>()

const forwardedProps = computed(() => props as Partial<TooltipTriggerProps>)
</script>

<template>
  <TooltipTrigger
    data-slot="tooltip-trigger"
    v-bind="forwardedProps"
  >
    <slot />
  </TooltipTrigger>
</template>
