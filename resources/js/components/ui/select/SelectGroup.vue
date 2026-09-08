<script setup lang="ts">
import { omitUndefinedProps } from '@/lib/utils'
import type { SelectGroupProps } from "reka-ui"
import { SelectGroup } from "reka-ui"
import { computed } from 'vue'

const props = defineProps<SelectGroupProps>()

const forwardedProps = computed(() => omitUndefinedProps(props))
</script>

<template>
  <SelectGroup
    data-slot="select-group"
    v-bind="forwardedProps"
  >
    <slot />
  </SelectGroup>
</template>
