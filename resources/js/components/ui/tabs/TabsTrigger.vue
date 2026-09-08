<script setup lang="ts">
import { omitUndefinedProps } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import type { TabsTriggerProps } from 'reka-ui'
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import { TabsTrigger, useForwardProps } from 'reka-ui'
import { computed } from 'vue'

const props = defineProps<
    TabsTriggerProps & {
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
    <TabsTrigger
        data-slot="tabs-trigger"
        v-bind="forwarded"
        :class="
            cn(
                appTheme.field.tabsTrigger,
                props.class,
            )
        "
    >
        <slot />
    </TabsTrigger>
</template>
