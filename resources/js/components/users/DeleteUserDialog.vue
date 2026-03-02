<script setup lang="ts">
    import { type User } from '@/types'

    const emit = defineEmits<{
        'update:open': [open: boolean]
        deleted: []
    }>()

    defineProps<{
        open: boolean
        user: User
    }>()

    const form = useForm({})
</script>

<template>
    <UiDialog :open="open" @update:open="emit('update:open', $event)">
        <UiDialogContent class="max-h-[calc(100svh-2rem)] overflow-y-auto sm:max-w-md">
            <UiDialogHeader>
                <UiDialogTitle>Delete User</UiDialogTitle>
                <UiDialogDescription> Are you sure you want to delete this user? This action cannot be undone. </UiDialogDescription>
            </UiDialogHeader>
            <div class="py-4">
                <div v-if="user" class="flex items-center gap-4">
                    <UserInfo :user="user" :show-email="true" />
                </div>
            </div>
            <UiDialogFooter>
                <UiButton type="button" variant="ghost" class="w-full sm:w-auto" @click="emit('update:open', false)" :disabled="form.processing">
                    Cancel
                </UiButton>
                <UiButton
                    variant="destructive"
                    class="w-full sm:w-auto"
                    @click="form.delete(route('app.admin.users.destroy', { user: user.id }), { onSuccess: () => emit('deleted') })"
                    :disabled="form.processing"
                >
                    <Icon-mdi-loading v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Delete User
                </UiButton>
            </UiDialogFooter>
        </UiDialogContent>
    </UiDialog>
</template>
