<script setup lang="ts">
    import type { User } from '@/types'
    import type { DataTableRowAction } from '@/types/base-ui'

    interface Props {
        currentUserId: number
        user: User
    }

    const props = defineProps<Props>()

    const emit = defineEmits<{
        delete: [user: User]
        edit: [user: User]
    }>()

    const actions = computed<Array<DataTableRowAction<unknown>>>(() => [
        {
            key: 'edit',
            label: 'Edit',
            onClick: () => emit('edit', props.user)
        },
        {
            key: 'delete',
            label: 'Delete',
            destructive: true,
            visible: () => props.currentUserId !== props.user.id,
            onClick: () => emit('delete', props.user)
        }
    ])
</script>

<template>
    <BaseMenuBaseActionMenu :actions="actions" :row="props.user" />
</template>
