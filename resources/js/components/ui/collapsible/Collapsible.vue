<script setup lang="ts">
import type { CollapsibleRootEmits, CollapsibleRootProps } from 'reka-ui'
import { omitUndefinedProps } from '@/lib/utils'
import { CollapsibleRoot, useForwardPropsEmits } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<CollapsibleRootProps>()
const emits = defineEmits<CollapsibleRootEmits>()

const forwarded = useForwardPropsEmits(computed(() => omitUndefinedProps(props)), emits)
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
