<script setup lang="ts">
import { computed } from 'vue'
import { omitUndefinedProps } from '@/lib/utils'
import type { TabsRootEmits, TabsRootProps } from 'reka-ui'
import { TabsRoot, useForwardPropsEmits } from 'reka-ui'

const props = defineProps<TabsRootProps>()
const emits = defineEmits<TabsRootEmits>()

const rawForwardedProps = useForwardPropsEmits(props, emits)
const forwarded = computed(() => omitUndefinedProps(rawForwardedProps.value))
</script>

<template>
    <TabsRoot data-slot="tabs" v-bind="forwarded">
        <slot />
    </TabsRoot>
</template>
