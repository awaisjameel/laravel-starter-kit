<script setup lang="ts">
import { omitUndefinedProps } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import type { TabsListProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
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

const rawForwardedProps = useForwardProps(delegatedProps)
const forwarded = computed(() => omitUndefinedProps(rawForwardedProps.value))
</script>

<template>
    <TabsList
        data-slot="tabs-list"
        v-bind="forwarded"
        :class="cn(appTheme.field.tabsList, props.class)"
    >
        <slot />
    </TabsList>
</template>
