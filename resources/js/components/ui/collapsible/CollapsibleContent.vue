<script setup lang="ts">
import { computed } from 'vue'
import { CollapsibleContent, type CollapsibleContentProps } from 'reka-ui'

const props = defineProps<CollapsibleContentProps>()

const forwardedProps = computed(() => props as Partial<CollapsibleContentProps>)
</script>

<template>
  <CollapsibleContent
    data-slot="collapsible-content"
    v-bind="forwardedProps"
  >
    <slot />
  </CollapsibleContent>
</template>
