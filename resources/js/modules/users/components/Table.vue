<script setup lang="ts">
    import type { User } from '@/types'
    import type { DataTableColumn, DataTableRowAction, MobileCardField } from '@/types/base-ui'

    type UserSortColumn = 'name' | 'email' | 'role' | 'created_at'

    interface Props {
        currentUserId: number
        users: User[]
        sortBy?: UserSortColumn
        sortDirection?: 'asc' | 'desc'
    }

    const props = withDefaults(defineProps<Props>(), {
        sortBy: undefined,
        sortDirection: undefined
    })

    const emit = defineEmits<{
        delete: [user: User]
        edit: [user: User]
        sort: [column: UserSortColumn]
    }>()

    const formatDate = (value: string): string => new Date(value).toLocaleDateString()
    const formatRole = (value: string): string => value.charAt(0).toUpperCase() + value.slice(1)

    const columns: Array<DataTableColumn<User, UserSortColumn>> = [
        {
            key: 'name',
            label: 'Name',
            sortable: true,
            sortKey: 'name',
            value: (row) => row.name
        },
        {
            key: 'role',
            label: 'Role',
            sortable: true,
            sortKey: 'role',
            class: 'capitalize',
            value: (row) => formatRole(row.role)
        },
        {
            key: 'email',
            label: 'Email',
            sortable: true,
            sortKey: 'email',
            value: (row) => row.email
        },
        {
            key: 'created_at',
            label: 'Created At',
            sortable: true,
            sortKey: 'created_at',
            value: (row) => formatDate(row.created_at)
        }
    ]

    const mobileFields: Array<MobileCardField<User>> = [
        {
            key: 'role',
            label: 'Role',
            value: (row) => formatRole(row.role)
        },
        {
            key: 'email',
            label: 'Email',
            class: 'break-all',
            value: (row) => row.email
        },
        {
            key: 'created_at',
            label: 'Created',
            value: (row) => formatDate(row.created_at)
        }
    ]

    const actions = computed<Array<DataTableRowAction<User>>>(() => [
        {
            key: 'edit',
            label: 'Edit',
            onClick: (row) => emit('edit', row)
        },
        {
            key: 'delete',
            label: 'Delete',
            destructive: true,
            visible: (row) => props.currentUserId !== row.id,
            onClick: (row) => emit('delete', row)
        }
    ])
</script>

<template>
    <BaseTableBaseDataTableMobileCards :rows="props.users" :row-key="(row) => row.id" :fields="mobileFields" :actions="actions">
        <template #mobile-header="{ row }">
            <div class="flex min-w-0 items-center gap-2">
                <UserInfo :user="row" />
            </div>
        </template>
    </BaseTableBaseDataTableMobileCards>

    <BaseTableBaseDataTable
        :rows="props.users"
        :columns="columns"
        :row-key="(row) => row.id"
        :actions="actions"
        :sort-by="props.sortBy"
        :sort-direction="props.sortDirection"
        empty-message="No users found."
        @sort="emit('sort', $event)"
    >
        <template #cell-name="{ row }">
            <div class="flex min-w-0 items-center gap-2">
                <UserInfo :user="row" />
            </div>
        </template>
    </BaseTableBaseDataTable>
</template>
