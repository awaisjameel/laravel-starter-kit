<script setup lang="ts">
    import RegisteredUserController from '@/actions/App/Modules/Auth/Http/Controllers/RegisteredUserController'
    import { buildRegisterFormFields, type RegisterFormValues } from '@/modules/auth/forms/register-form-schema'

    const { form, submit } = useResourceForm<RegisterFormValues>({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    })

    const fields = buildRegisterFormFields()

    const submitForm = () => {
        submit(RegisteredUserController.store(), {
            onFinish: () => {
                form.reset('password', 'password_confirmation')
            }
        })
    }
</script>

<template>
    <AuthLayout title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <BaseFormsBaseFormRenderer :model="form" :fields="fields" :errors="form.errors" :processing="form.processing" @submit="submitForm">
            <template #actions>
                <div class="space-y-4">
                    <BaseButton type="submit" full-width :loading="form.processing" label="Create account" />
                    <div class="text-center text-sm text-muted-foreground">
                        Already have an account?
                        <TextLink :href="route('auth.login.create')" class="underline underline-offset-4">Log in</TextLink>
                    </div>
                </div>
            </template>
        </BaseFormsBaseFormRenderer>
    </AuthLayout>
</template>
