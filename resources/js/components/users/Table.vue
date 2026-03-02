<script setup lang="ts">
    import type { User } from '@/types'
    import type { DataTableColumn, DataTableRowAction, MobileCardField } from '@/types/base-ui'

    interface Props {
        currentUserId: number
        users: User[]
        sortBy?: string
        sortDirection?: 'asc' | 'desc'
    }

    const props = withDefaults(defineProps<Props>(), {
        sortBy: undefined,
        sortDirection: undefined
    })

    const emit = defineEmits<{
        delete: [user: User]
        edit: [user: User]
        sort: [column: string]
    }>()

    const formatDate = (value: string): string => new Date(value).toLocaleDateString()
    const formatRole = (value: string): string => value.charAt(0).toUpperCase() + value.slice(1)

    const columns: Array<DataTableColumn<unknown, string>> = [
        {
            key: 'name',
            label: 'Name',
            sortable: true,
            sortKey: 'name',
            value: (row) => (row as User).name
        },
        {
            key: 'role',
            label: 'Role',
            sortable: true,
            sortKey: 'role',
            class: 'capitalize',
            value: (row) => formatRole((row as User).role)
        },
        {
            key: 'email',
            label: 'Email',
            sortable: true,
            sortKey: 'email',
            value: (row) => (row as User).email
        },
        {
            key: 'created_at',
            label: 'Created At',
            sortable: true,
            sortKey: 'created_at',
            value: (row) => formatDate((row as User).created_at)
        }
    ]

    const mobileFields: Array<MobileCardField<unknown>> = [
        {
            key: 'role',
            label: 'Role',
            value: (row) => formatRole((row as User).role)
        },
        {
            key: 'email',
            label: 'Email',
            class: 'break-all',
            value: (row) => (row as User).email
        },
        {
            key: 'created_at',
            label: 'Created',
            value: (row) => formatDate((row as User).created_at)
        }
    ]

    const actions = computed<Array<DataTableRowAction<unknown>>>(() => [
        {
            key: 'edit',
            label: 'Edit',
            onClick: (row) => emit('edit', row as User)
        },
        {
            key: 'delete',
            label: 'Delete',
            destructive: true,
            visible: (row) => props.currentUserId !== (row as User).id,
            onClick: (row) => emit('delete', row as User)
        }
    ])
</script>

<template>
    <BaseTableBaseDataTableMobileCards :rows="props.users" :row-key="(row) => (row as User).id" :fields="mobileFields" :actions="actions">
        <template #mobile-header="{ row }">
            <div class="flex min-w-0 items-center gap-2">
                <UserInfo :user="row as User" />
            </div>
        </template>
    </BaseTableBaseDataTableMobileCards>

    <BaseTableBaseDataTable
        :rows="props.users"
        :columns="columns"
        :row-key="(row) => (row as User).id"
        :actions="actions"
        :sort-by="props.sortBy"
        :sort-direction="props.sortDirection"
        empty-message="No users found."
        @sort="emit('sort', $event)"
    >
        <template #cell-name="{ row }">
            <div class="flex min-w-0 items-center gap-2">
                <UserInfo :user="row as User" />
            </div>
        </template>
    </BaseTableBaseDataTable>
</template>
