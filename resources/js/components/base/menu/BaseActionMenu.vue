<script setup lang="ts">
    import type { DataTableRowAction } from '@/types/base-ui'

    interface Props {
        actions: Array<DataTableRowAction<unknown>>
        row: unknown
        triggerLabel?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        triggerLabel: 'Open actions'
    })

    const isVisible = (action: DataTableRowAction<unknown>): boolean => {
        if (typeof action.visible === 'function') {
            return action.visible(props.row)
        }

        return action.visible ?? true
    }

    const isDisabled = (action: DataTableRowAction<unknown>): boolean => {
        if (typeof action.disabled === 'function') {
            return action.disabled(props.row)
        }

        return action.disabled ?? false
    }

    const handleAction = (action: DataTableRowAction<unknown>): void => {
        if (isDisabled(action)) {
            return
        }

        action.onClick(props.row)
    }
</script>

<template>
    <UiDropdownMenu>
        <UiDropdownMenuTrigger as-child>
            <BaseButton variant="ghost" size="icon" class="h-8 w-8 p-0">
                <span class="sr-only">{{ props.triggerLabel }}</span>
                <Icon-mdi-dots-vertical class="h-4 w-4" />
            </BaseButton>
        </UiDropdownMenuTrigger>
        <UiDropdownMenuContent align="end">
            <UiDropdownMenuItem
                v-for="action in props.actions"
                v-show="isVisible(action)"
                :key="action.key"
                :class="action.destructive ? 'text-destructive focus:text-destructive' : undefined"
                :disabled="isDisabled(action)"
                @click="handleAction(action)"
            >
                <component :is="action.icon" v-if="action.icon !== undefined" class="mr-2 size-4" />
                {{ action.label }}
            </UiDropdownMenuItem>
        </UiDropdownMenuContent>
    </UiDropdownMenu>
</template>
