<script setup lang="ts">
import { computed } from 'vue'
import { DropdownMenuGroup, type DropdownMenuGroupProps } from 'reka-ui'

const props = defineProps<DropdownMenuGroupProps>()

const forwardedProps = computed(() => props as Partial<DropdownMenuGroupProps>)
</script>

<template>
  <DropdownMenuGroup
    data-slot="dropdown-menu-group"
    v-bind="forwardedProps"
  >
    <slot />
  </DropdownMenuGroup>
</template>
