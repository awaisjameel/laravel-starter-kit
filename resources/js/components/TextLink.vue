<script setup lang="ts">
    import { Method } from '@inertiajs/core'

    interface Props {
        href: string
        tabindex?: number
        method?: Method
        as?: string
    }

    const props = defineProps<Props>()

    const linkProps = computed(() => {
        const resolved: {
            href: string
            tabindex?: number
            method?: Method
            as?: string
        } = {
            href: props.href
        }

        if (props.tabindex !== undefined) {
            resolved.tabindex = props.tabindex
        }

        if (props.method !== undefined) {
            resolved.method = props.method
        }

        if (props.as !== undefined) {
            resolved.as = props.as
        }

        return resolved
    })
</script>

<template>
    <Link
        v-bind="linkProps"
        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
    >
        <slot />
    </Link>
</template>
