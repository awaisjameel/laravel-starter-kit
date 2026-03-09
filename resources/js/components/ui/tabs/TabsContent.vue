<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { TabsContentProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { TabsContent, useForwardProps } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<
    TabsContentProps & {
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
    <TabsContent data-slot="tabs-content" v-bind="forwarded" :class="cn('flex-1 outline-none', props.class)">
        <slot />
    </TabsContent>
</template>
