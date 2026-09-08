<script setup lang="ts">
import { omitUndefinedProps } from '@/lib/utils'
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

const rawForwardedProps = useForwardProps(delegatedProps)
const forwarded = computed(() => omitUndefinedProps(rawForwardedProps.value))
</script>

<template>
    <TabsContent data-slot="tabs-content" v-bind="forwarded" :class="cn('flex-1 outline-none', props.class)">
        <slot />
    </TabsContent>
</template>
