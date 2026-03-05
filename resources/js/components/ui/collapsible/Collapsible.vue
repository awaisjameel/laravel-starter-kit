<script setup lang="ts">
import type { CollapsibleRootEmits, CollapsibleRootProps } from 'reka-ui'
import { CollapsibleRoot, useForwardPropsEmits } from 'reka-ui'

const props = defineProps<CollapsibleRootProps>()
const emits = defineEmits<CollapsibleRootEmits>()

const forwarded = useForwardPropsEmits(props, emits) as unknown as Record<string, unknown>
</script>

<template>
  <CollapsibleRoot
    v-slot="{ open }"
    data-slot="collapsible"
    v-bind="forwarded"
  >
    <slot :open="open" />
  </CollapsibleRoot>
</template>
