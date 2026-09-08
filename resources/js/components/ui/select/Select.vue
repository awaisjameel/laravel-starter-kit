<script setup lang="ts">
import { computed } from 'vue'
import { omitUndefinedProps } from '@/lib/utils'
import type { SelectRootEmits, SelectRootProps } from "reka-ui"
import { SelectRoot, useForwardPropsEmits } from "reka-ui"

const props = defineProps<SelectRootProps>()
const emits = defineEmits<SelectRootEmits>()

const rawForwardedProps = useForwardPropsEmits(props, emits)
const forwarded = computed(() => omitUndefinedProps(rawForwardedProps.value))
</script>

<template>
  <SelectRoot
    data-slot="select"
    v-bind="forwarded"
  >
    <slot />
  </SelectRoot>
</template>
