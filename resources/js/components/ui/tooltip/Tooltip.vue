<script setup lang="ts">
import { computed } from 'vue'
import { TooltipRoot, type TooltipRootEmits, type TooltipRootProps, useForwardPropsEmits } from 'reka-ui'

const props = defineProps<TooltipRootProps>()
const emits = defineEmits<TooltipRootEmits>()

const forwarded = useForwardPropsEmits(computed(() => props as Partial<TooltipRootProps>), emits)
</script>

<template>
  <TooltipRoot
    data-slot="tooltip"
    v-bind="forwarded"
  >
    <slot />
  </TooltipRoot>
</template>
