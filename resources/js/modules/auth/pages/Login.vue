<script setup lang="ts">
    import AuthenticatedSessionController from '@/actions/App/Modules/Auth/Http/Controllers/AuthenticatedSessionController'
    import { buildLoginFormFields, type LoginFormValues } from '@/modules/auth/forms/login-form-schema'
    import authRoutes from '@/routes/auth'

    defineProps<{
        status?: string
        canResetPassword: boolean
    }>()

    const { form, submit } = useResourceForm<LoginFormValues>({
        email: '',
        password: '',
        remember: false
    })

    const fields = buildLoginFormFields()

    const submitForm = () => {
        submit(AuthenticatedSessionController.store(), {
            onFinish: () => {
                form.reset('password')
            }
        })
    }
</script>

<template>
    <AuthLayout title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <BaseFormsBaseFormRenderer
            :model="form"
            :fields="fields"
            :errors="form.errors"
            :processing="form.processing"
            submit-label="Log in"
            @submit="submitForm"
        >
            <template #actions>
                <div class="space-y-4">
                    <BaseButton type="submit" full-width :loading="form.processing" label="Log in" />
                    <div class="flex items-center justify-between">
                        <TextLink v-if="canResetPassword" :href="authRoutes.password.request.url()" class="text-sm">Forgot password?</TextLink>
                    </div>
                </div>
            </template>
        </BaseFormsBaseFormRenderer>
    </AuthLayout>
</template>
