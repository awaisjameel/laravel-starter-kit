<script setup lang="ts">
import { computed } from 'vue'
import { TooltipProvider, type TooltipProviderProps } from 'reka-ui'

const props = withDefaults(defineProps<TooltipProviderProps>(), {
  delayDuration: 0,
})

const forwardedProps = computed(() => props as Partial<TooltipProviderProps>)
</script>

<template>
  <TooltipProvider v-bind="forwardedProps">
    <slot />
  </TooltipProvider>
</template>
