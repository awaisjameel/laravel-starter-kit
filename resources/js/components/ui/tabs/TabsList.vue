<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { TabsListProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { TabsList, useForwardProps } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<
    TabsListProps & {
        class?: HTMLAttributes['class']
    }
>()

const delegatedProps = computed(() => {
    const { class: _class, ...delegated } = props
    return delegated
})

const forwarded = useForwardProps(delegatedProps)
</script>

<template>
    <TabsList
        data-slot="tabs-list"
        v-bind="forwarded"
        :class="cn('bg-muted text-muted-foreground inline-flex h-9 w-fit items-center justify-center rounded-lg p-[3px]', props.class)"
    >
        <slot />
    </TabsList>
</template>
