<script setup lang="ts">
import { computed } from 'vue'
import { DialogRoot, type DialogRootEmits, type DialogRootProps, useForwardPropsEmits } from 'reka-ui'

const props = defineProps<DialogRootProps>()
const emits = defineEmits<DialogRootEmits>()

const forwarded = useForwardPropsEmits(computed(() => props as Partial<DialogRootProps>), emits)
</script>

<template>
  <DialogRoot
    data-slot="dialog"
    v-bind="forwarded"
  >
    <slot />
  </DialogRoot>
</template>
