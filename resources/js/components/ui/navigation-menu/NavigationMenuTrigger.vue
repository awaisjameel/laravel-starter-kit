<script setup lang="ts">
import { cn } from '@/lib/utils'
import {
    NavigationMenuTrigger,
    type NavigationMenuTriggerProps,
    useForwardProps,
} from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'
import { navigationMenuTriggerStyles } from '.'

const props = defineProps<NavigationMenuTriggerProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated as Partial<NavigationMenuTriggerProps>
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
    <NavigationMenuTrigger data-slot="navigation-menu-trigger" v-bind="forwardedProps"
        :class="cn(navigationMenuTriggerStyles, props.class)">
        <slot />
        <IconLucideChevronDown class="relative top-[1px] ml-1 size-3 transition duration-300 group-data-[state=open]:rotate-180"
            aria-hidden="true" />
    </NavigationMenuTrigger>
</template>
