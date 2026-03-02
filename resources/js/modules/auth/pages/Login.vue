<script setup lang="ts">
    import AuthenticatedSessionController from '@/actions/App/Modules/Auth/Http/Controllers/AuthenticatedSessionController'
    import type { FormFieldSchema } from '@/types/base-ui'

    defineProps<{
        status?: string
        canResetPassword: boolean
    }>()

    const { form, submit } = useResourceForm({
        email: '',
        password: '',
        remember: false
    })

    const fields: Array<FormFieldSchema<Record<string, unknown>>> = [
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
            autocomplete: 'current-password',
            placeholder: 'Password'
        },
        {
            name: 'remember',
            label: 'Remember me',
            type: 'checkbox',
            placeholder: 'Remember me'
        }
    ]

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
            :model="form as unknown as Record<string, unknown>"
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
                        <TextLink v-if="canResetPassword" :href="route('auth.password.request')" class="text-sm">Forgot password?</TextLink>
                    </div>
                </div>
            </template>
        </BaseFormsBaseFormRenderer>
    </AuthLayout>
</template>
