<script setup lang="ts">
import { computed } from 'vue'
import { DialogTrigger, type DialogTriggerProps } from 'reka-ui'

const props = defineProps<DialogTriggerProps>()

const forwardedProps = computed(() => props as Partial<DialogTriggerProps>)
</script>

<template>
  <DialogTrigger
    data-slot="sheet-trigger"
    v-bind="forwardedProps"
  >
    <slot />
  </DialogTrigger>
</template>
