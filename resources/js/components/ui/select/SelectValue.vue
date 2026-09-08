<script setup lang="ts">
import { omitUndefinedProps } from '@/lib/utils'
import type { SelectValueProps } from "reka-ui"
import { SelectValue } from "reka-ui"
import { computed } from 'vue'

const props = defineProps<SelectValueProps>()

const forwardedProps = computed(() => omitUndefinedProps(props))
</script>

<template>
  <SelectValue
    data-slot="select-value"
    v-bind="forwardedProps"
  >
    <slot />
  </SelectValue>
</template>
