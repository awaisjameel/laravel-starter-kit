<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import { type User } from '@/types'

    const emit = defineEmits<{
        'update:open': [open: boolean]
        deleted: []
    }>()

    const props = defineProps<{
        open: boolean
        user: User
    }>()

    const { form, submit } = useResourceForm({})

    const handleConfirm = () => {
        submit(UserController.destroy({ user: props.user.id }), {
            onSuccess: () => {
                emit('deleted')
                emit('update:open', false)
            }
        })
    }
</script>

<template>
    <BaseDialogBaseConfirmDialog
        :open="open"
        title="Delete User"
        description="Are you sure you want to delete this user? This action cannot be undone."
        confirm-label="Delete User"
        :processing="form.processing"
        @confirm="handleConfirm"
        @update:open="emit('update:open', $event)"
    >
        <div class="py-4">
            <div class="flex items-center gap-4">
                <UserInfo :user="props.user" :show-email="true" />
            </div>
        </div>
    </BaseDialogBaseConfirmDialog>
</template>
