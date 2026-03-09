<script setup lang="ts">
import { computed } from 'vue'
import {
  DropdownMenuSub,
  type DropdownMenuSubEmits,
  type DropdownMenuSubProps,
  useForwardPropsEmits,
} from 'reka-ui'

const props = defineProps<DropdownMenuSubProps>()
const emits = defineEmits<DropdownMenuSubEmits>()

const forwarded = useForwardPropsEmits(computed(() => props as Partial<DropdownMenuSubProps>), emits)
</script>

<template>
  <DropdownMenuSub data-slot="dropdown-menu-sub" v-bind="forwarded">
    <slot />
  </DropdownMenuSub>
</template>
