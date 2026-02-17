<script setup lang="ts">
    import type { Paginated } from '@/types'
    import { computed } from 'vue'

    /**
     * Reusable data table component with support for columns, rows, and actions.
     * Follows the project's table patterns from users/Index.vue.
     */

    export interface TableColumn<T = unknown> {
        key: string
        label: string
        class?: string
        headerClass?: string
        render?: (row: T) => string
    }

    export interface TableAction<T = unknown> {
        label: string
        icon?: unknown
        handler: (row: T) => void
        condition?: (row: T) => boolean
        class?: string
    }

    const props = defineProps<{
        columns: TableColumn<T>[]
        data: T[] | Paginated<T>
        actions?: TableAction<T>[]
        actionsLabel?: string
        emptyMessage?: string
        loading?: boolean
    }>()

    const emit = defineEmits<{
        'row-click': [row: T]
    }>()

    type T = Record<string, unknown>

    const rows = computed(() => {
        return Array.isArray(props.data) ? props.data : props.data.data
    })

    const hasActions = computed(() => props.actions && props.actions.length > 0)

    const getCellValue = (row: T, column: TableColumn<T>): string => {
        if (column.render) {
            return column.render(row)
        }
        const value = row[column.key]
        if (value === null || value === undefined) {
            return ''
        }
        if (typeof value === 'object') {
            return JSON.stringify(value)
        }
        return String(value)
    }
</script>

<template>
    <div class="relative min-h-[320px]">
        <div class="relative overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead class="border-b">
                    <tr class="hover:bg-transparent">
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="['h-12 px-4 text-left align-middle font-medium text-muted-foreground', column.headerClass]"
                        >
                            {{ column.label }}
                        </th>
                        <th v-if="hasActions" class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">
                            {{ actionsLabel || 'Actions' }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, index) in rows"
                        :key="index"
                        class="cursor-pointer border-b transition-colors hover:bg-muted/50"
                        @click="emit('row-click', row)"
                    >
                        <td v-for="column in columns" :key="column.key" :class="['p-4', column.class]">
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                {{ getCellValue(row, column) }}
                            </slot>
                        </td>
                        <td v-if="hasActions" class="p-4 text-right">
                            <UiDropdownMenu>
                                <UiDropdownMenuTrigger as-child>
                                    <UiButton variant="ghost" class="h-8 w-8 p-0" @click.stop>
                                        <span class="sr-only">Open menu</span>
                                        <Icon-mdi-dots-vertical class="h-4 w-4" />
                                    </UiButton>
                                </UiDropdownMenuTrigger>
                                <UiDropdownMenuContent align="end">
                                    <template v-for="(action, actionIndex) in actions" :key="actionIndex">
                                        <UiDropdownMenuItem
                                            v-if="!action.condition || action.condition(row)"
                                            @click.stop="action.handler(row)"
                                            :class="action.class"
                                        >
                                            {{ action.label }}
                                        </UiDropdownMenuItem>
                                    </template>
                                </UiDropdownMenuContent>
                            </UiDropdownMenu>
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0 && !loading">
                        <td :colspan="columns.length + (hasActions ? 1 : 0)" class="p-8 text-center text-muted-foreground">
                            {{ emptyMessage || 'No data available' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
