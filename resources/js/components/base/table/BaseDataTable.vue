<script setup lang="ts">
    import type { DataTableColumn, DataTableRowAction, SortDirection } from '@/types/base-ui'
    import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next'

    interface Props {
        rows: unknown[]
        columns: Array<DataTableColumn<unknown, string>>
        rowKey: (row: unknown) => string | number
        actions?: Array<DataTableRowAction<unknown>>
        sortBy?: string
        sortDirection?: SortDirection
        emptyMessage?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        actions: () => [],
        sortBy: undefined,
        sortDirection: undefined,
        emptyMessage: 'No records found.'
    })

    const emit = defineEmits<{
        sort: [sortKey: string]
    }>()

    const isSortable = (column: DataTableColumn<unknown, string>): boolean => column.sortable === true && column.sortKey !== undefined

    const resolveSortIcon = (column: DataTableColumn<unknown, string>) => {
        if (!isSortable(column)) {
            return null
        }

        if (props.sortBy !== column.sortKey) {
            return ArrowUpDown
        }

        return props.sortDirection === 'asc' ? ArrowUp : ArrowDown
    }
</script>

<template>
    <UiCard>
        <UiCardContent class="p-0">
            <div class="hidden md:block">
                <div class="relative overflow-x-auto">
                    <table class="w-full min-w-[700px]">
                        <thead class="border-b">
                            <tr class="hover:bg-transparent">
                                <th
                                    v-for="column in props.columns"
                                    :key="column.key"
                                    class="h-12 px-4 text-left align-middle font-medium text-muted-foreground"
                                    :class="column.headerClass"
                                >
                                    <button
                                        v-if="isSortable(column)"
                                        type="button"
                                        class="inline-flex items-center gap-1"
                                        @click="emit('sort', column.sortKey ?? column.key)"
                                    >
                                        {{ column.label }}
                                        <component :is="resolveSortIcon(column)" class="size-4" />
                                    </button>
                                    <span v-else>{{ column.label }}</span>
                                </th>
                                <th v-if="props.actions.length > 0" class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in props.rows" :key="props.rowKey(row)" class="border-b transition-colors hover:bg-muted/50">
                                <td v-for="column in props.columns" :key="column.key" class="p-4" :class="column.class">
                                    <slot :name="`cell-${column.key}`" :row="row">
                                        {{ column.value(row) }}
                                    </slot>
                                </td>
                                <td v-if="props.actions.length > 0" class="p-4 text-right">
                                    <slot name="actions" :row="row">
                                        <BaseMenuBaseActionMenu :actions="props.actions" :row="row" />
                                    </slot>
                                </td>
                            </tr>
                            <tr v-if="props.rows.length === 0">
                                <td
                                    :colspan="props.columns.length + (props.actions.length > 0 ? 1 : 0)"
                                    class="p-8 text-center text-muted-foreground"
                                >
                                    {{ props.emptyMessage }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </UiCardContent>
    </UiCard>
</template>
