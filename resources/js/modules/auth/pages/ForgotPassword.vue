<script setup lang="ts">
    import PasswordResetLinkController from '@/actions/App/Modules/Auth/Http/Controllers/PasswordResetLinkController'
    import type { FormFieldSchema } from '@/types/base-ui'

    defineProps<{
        status?: string
    }>()

    const { form, submit } = useResourceForm({
        email: ''
    })

    const fields: Array<FormFieldSchema<Record<string, unknown>>> = [
        {
            name: 'email',
            label: 'Email address',
            type: 'email',
            required: true,
            placeholder: 'email@example.com'
        }
    ]

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
                :model="form as unknown as Record<string, unknown>"
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
                <TextLink :href="route('auth.login.create')">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
