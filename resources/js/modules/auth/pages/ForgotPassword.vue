<script setup lang="ts">
    import PasswordResetLinkController from '@/actions/App/Modules/Auth/Http/Controllers/PasswordResetLinkController'
    import { buildForgotPasswordFormFields, type ForgotPasswordFormValues } from '@/modules/auth/forms/forgot-password-form-schema'
    import authRoutes from '@/routes/auth'

    defineProps<{
        status?: string
    }>()

    const { form, submit } = useResourceForm<ForgotPasswordFormValues>({
        email: ''
    })

    const fields = buildForgotPasswordFormFields()

    const submitForm = () => {
        submit(PasswordResetLinkController.store())
    }
</script>

<template>
    <AuthLayout title="Forgot password" description="Enter your email to receive a password reset link">
        <Head title="Forgot password" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="space-y-6">
            <BaseFormsBaseFormRenderer
                :model="form"
                :fields="fields"
                :errors="form.errors"
                :processing="form.processing"
                submit-label="Email password reset link"
                @submit="submitForm"
            >
                <template #actions>
                    <BaseButton type="submit" full-width :loading="form.processing" label="Email password reset link" />
                </template>
            </BaseFormsBaseFormRenderer>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Or, return to</span>
                <TextLink :href="authRoutes.login.create.url()">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
