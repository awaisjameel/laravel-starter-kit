<script setup lang="ts">
    import NewPasswordController from '@/actions/App/Modules/Auth/Http/Controllers/NewPasswordController'
    import { createResetPasswordFormDefaults, resetPasswordFormContract } from '@/modules/auth/forms/reset-password-form-schema'

    interface Props {
        token: string
        email: string
    }

    const props = defineProps<Props>()

    const { form, fields, submit } = useSchemaResourceForm(resetPasswordFormContract, createResetPasswordFormDefaults(props.token, props.email))

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
            :model="form"
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
