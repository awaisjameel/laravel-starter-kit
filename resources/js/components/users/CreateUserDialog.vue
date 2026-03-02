<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import { buildUserFormFields } from './user-form-schema'

    const emit = defineEmits<{
        'update:open': [open: boolean]
        created: []
    }>()

    defineProps<{
        open: boolean
    }>()

    const { form, submit } = useResourceForm({
        name: '',
        email: '',
        role: '',
        password: ''
    })

    const fields = buildUserFormFields(false)

    const submitForm = () => {
        submit(UserController.store(), {
            resetOnSuccess: true,
            onSuccess: () => {
                emit('created')
                emit('update:open', false)
            }
        })
    }
</script>

<template>
    <BaseDialog
        :open="open"
        title="Create User"
        description="Add a new user to the system"
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
            submit-label="Create User"
            cancel-label="Cancel"
            :show-cancel="true"
            @submit="submitForm"
            @cancel="emit('update:open', false)"
        />
    </BaseDialog>
</template>
