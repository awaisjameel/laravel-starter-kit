<script setup lang="ts">
    import type { DataTableRowAction, MobileCardField } from '@/types/base-ui'

    interface Props {
        rows: unknown[]
        rowKey: (row: unknown) => string | number
        fields: Array<MobileCardField<unknown>>
        actions?: Array<DataTableRowAction<unknown>>
        emptyMessage?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        actions: () => [],
        emptyMessage: 'No records found.'
    })
</script>

<template>
    <div class="grid gap-3 md:hidden">
        <article v-for="row in props.rows" :key="props.rowKey(row)" class="rounded-lg border border-border/70 bg-card p-3 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1">
                    <slot name="mobile-header" :row="row" />
                </div>
                <BaseMenuBaseActionMenu v-if="props.actions.length > 0" :actions="props.actions" :row="row" />
            </div>

            <dl class="mt-4 grid gap-3 text-sm">
                <div v-for="field in props.fields" :key="field.key" class="grid gap-1">
                    <dt class="text-xs tracking-wide text-muted-foreground uppercase">{{ field.label }}</dt>
                    <dd :class="field.class">{{ field.value(row) }}</dd>
                </div>
            </dl>
        </article>

        <div v-if="props.rows.length === 0" class="rounded-lg border border-dashed border-border/70 p-6 text-center text-sm text-muted-foreground">
            {{ props.emptyMessage }}
        </div>
    </div>
</template>
