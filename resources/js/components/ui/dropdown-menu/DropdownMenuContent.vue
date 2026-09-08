<script setup lang="ts">
import { cn } from '@/lib/utils'
import { appTheme } from '@/lib/theme'
import {
    DropdownMenuContent,
    type DropdownMenuContentEmits,
    type DropdownMenuContentProps,
    DropdownMenuPortal,
    useForwardPropsEmits,
} from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = withDefaults(
    defineProps<DropdownMenuContentProps & { class?: HTMLAttributes['class'] }>(),
    {
        sideOffset: 4,
    },
)
const emits = defineEmits<DropdownMenuContentEmits>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props

    return delegated as Partial<DropdownMenuContentProps>
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
    <DropdownMenuPortal>
        <DropdownMenuContent data-slot="dropdown-menu-content" v-bind="forwarded"
            :class="cn(appTheme.floating.content, 'origin-(--reka-dropdown-menu-content-transform-origin)', props.class)">
            <slot />
        </DropdownMenuContent>
    </DropdownMenuPortal>
</template>
