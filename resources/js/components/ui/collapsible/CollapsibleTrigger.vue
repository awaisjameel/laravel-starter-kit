<script setup lang="ts">
import { computed } from 'vue'
import { CollapsibleTrigger, type CollapsibleTriggerProps } from 'reka-ui'

const props = defineProps<CollapsibleTriggerProps>()

const forwardedProps = computed(() => props as Partial<CollapsibleTriggerProps>)
</script>

<template>
  <CollapsibleTrigger
    data-slot="collapsible-trigger"
    v-bind="forwardedProps"
  >
    <slot />
  </CollapsibleTrigger>
</template>
