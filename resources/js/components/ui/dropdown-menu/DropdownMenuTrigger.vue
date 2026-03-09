<script setup lang="ts">
import { computed } from 'vue'
import { DropdownMenuTrigger, type DropdownMenuTriggerProps, useForwardProps } from 'reka-ui'

const props = defineProps<DropdownMenuTriggerProps>()

const forwardedProps = useForwardProps(computed(() => props as Partial<DropdownMenuTriggerProps>))
</script>

<template>
    <DropdownMenuTrigger data-slot="dropdown-menu-trigger" v-bind="forwardedProps">
        <slot />
    </DropdownMenuTrigger>
</template>
