<script setup lang="ts">
    import NewPasswordController from '@/actions/App/Modules/Auth/Http/Controllers/NewPasswordController'
    import type { FormFieldSchema } from '@/types/base-ui'

    interface Props {
        token: string
        email: string
    }

    const props = defineProps<Props>()

    const { form, submit } = useResourceForm({
        token: props.token,
        email: props.email,
        password: '',
        password_confirmation: ''
    })

    const fields: Array<FormFieldSchema<Record<string, unknown>>> = [
        {
            name: 'email',
            label: 'Email',
            type: 'email',
            readonly: true
        },
        {
            name: 'password',
            label: 'Password',
            type: 'password',
            required: true,
            autocomplete: 'new-password',
            placeholder: 'Password'
        },
        {
            name: 'password_confirmation',
            label: 'Confirm Password',
            type: 'password',
            required: true,
            autocomplete: 'new-password',
            placeholder: 'Confirm password'
        }
    ]

    const submitForm = () => {
        submit(NewPasswordController.store(), {
            onFinish: () => {
                form.reset('password', 'password_confirmation')
            }
        })
    }
</script>

<template>
    <AuthLayout title="Reset password" description="Please enter your new password below">
        <Head title="Reset password" />

        <BaseFormsBaseFormRenderer
            :model="form as unknown as Record<string, unknown>"
            :fields="fields"
            :errors="form.errors"
            :processing="form.processing"
            submit-label="Reset password"
            @submit="submitForm"
        >
            <template #actions>
                <BaseButton type="submit" full-width :loading="form.processing" label="Reset password" />
            </template>
        </BaseFormsBaseFormRenderer>
    </AuthLayout>
</template>
