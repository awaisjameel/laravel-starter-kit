<script setup lang="ts">
import { computed } from 'vue'
import { DropdownMenuRoot, type DropdownMenuRootEmits, type DropdownMenuRootProps, useForwardPropsEmits } from 'reka-ui'

const props = defineProps<DropdownMenuRootProps>()
const emits = defineEmits<DropdownMenuRootEmits>()

const forwarded = useForwardPropsEmits(computed(() => props as Partial<DropdownMenuRootProps>), emits)
</script>

<template>
  <DropdownMenuRoot
    data-slot="dropdown-menu"
    v-bind="forwarded"
  >
    <slot />
  </DropdownMenuRoot>
</template>
