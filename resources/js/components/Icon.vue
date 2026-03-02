<script setup lang="ts">
    import * as icons from 'lucide-vue-next'
    import type { Component } from 'vue'

    interface Props {
        name: string
        class?: string
        size?: number | string
        color?: string
        strokeWidth?: number | string
    }

    const props = withDefaults(defineProps<Props>(), {
        class: '',
        size: 16,
        strokeWidth: 2
    })

    const className = computed(() => cn('h-4 w-4', props.class))
    const iconRegistry = icons as unknown as Record<string, unknown>
    const isVueComponent = (value: unknown): value is Component => typeof value === 'function' || (typeof value === 'object' && value !== null)

    const icon = computed<Component | null>(() => {
        const iconName = props.name.charAt(0).toUpperCase() + props.name.slice(1)
        const candidate = iconRegistry[iconName]

        return isVueComponent(candidate) ? candidate : null
    })
</script>

<template>
    <component :is="icon" :class="className" :size="size" :stroke-width="strokeWidth" :color="color" />
</template>
