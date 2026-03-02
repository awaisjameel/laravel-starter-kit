<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import { type User } from '@/types'
    import { buildUserFormFields } from './user-form-schema'

    const emit = defineEmits<{
        'update:open': [open: boolean]
        updated: []
    }>()

    const props = defineProps<{
        open: boolean
        user: User
    }>()

    const { form, submit } = useResourceForm({
        name: props.user?.name || '',
        email: props.user?.email || '',
        role: props.user?.role || '',
        password: ''
    })

    const fields = buildUserFormFields(true)

    watch(
        () => props.user,
        (user) => {
            form.name = user.name
            form.email = user.email
            form.role = user.role
            form.password = ''
        },
        { immediate: true }
    )

    const submitForm = () => {
        submit(UserController.update({ user: props.user.id }), {
            onSuccess: () => {
                emit('updated')
                emit('update:open', false)
            }
        })
    }
</script>

<template>
    <BaseDialog
        :open="open"
        title="Edit User"
        description="Make changes to the user's information"
        :processing="form.processing"
        :show-footer="false"
        :show-cancel="false"
        max-width-class="sm:max-w-[425px]"
        @update:open="emit('update:open', $event)"
    >
        <BaseFormsBaseFormRenderer
            :model="form as unknown as Record<string, unknown>"
            :fields="fields"
            :errors="form.errors"
            :processing="form.processing"
            submit-label="Save Changes"
            cancel-label="Cancel"
            :show-cancel="true"
            @submit="submitForm"
            @cancel="emit('update:open', false)"
        />
    </BaseDialog>
</template>
