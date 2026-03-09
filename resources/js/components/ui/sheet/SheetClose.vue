<script setup lang="ts">
import { computed } from 'vue'
import { DialogClose, type DialogCloseProps } from 'reka-ui'

const props = defineProps<DialogCloseProps>()

const forwardedProps = computed(() => props as Partial<DialogCloseProps>)
</script>

<template>
  <DialogClose
    data-slot="sheet-close"
    v-bind="forwardedProps"
  >
    <slot />
  </DialogClose>
</template>
