<script setup lang="ts">
    import RegisteredUserController from '@/actions/App/Modules/Auth/Http/Controllers/RegisteredUserController'
    import type { FormFieldSchema } from '@/types/base-ui'

    const { form, submit } = useResourceForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    })

    const fields: Array<FormFieldSchema<Record<string, unknown>>> = [
        {
            name: 'name',
            label: 'Name',
            type: 'text',
            required: true,
            autocomplete: 'name',
            placeholder: 'Full name'
        },
        {
            name: 'email',
            label: 'Email address',
            type: 'email',
            required: true,
            autocomplete: 'email',
            placeholder: 'email@example.com'
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
            label: 'Confirm password',
            type: 'password',
            required: true,
            autocomplete: 'new-password',
            placeholder: 'Confirm password'
        }
    ]

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

        <BaseFormsBaseFormRenderer
            :model="form as unknown as Record<string, unknown>"
            :fields="fields"
            :errors="form.errors"
            :processing="form.processing"
            @submit="submitForm"
        >
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
