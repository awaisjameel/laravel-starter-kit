<script setup lang="ts">
    import ConfirmablePasswordController from '@/actions/App/Modules/Auth/Http/Controllers/ConfirmablePasswordController'
    import type { FormFieldSchema } from '@/types/base-ui'

    const { form, submit } = useResourceForm({
        password: ''
    })

    const fields: Array<FormFieldSchema<Record<string, unknown>>> = [
        {
            name: 'password',
            label: 'Password',
            type: 'password',
            required: true,
            autocomplete: 'current-password',
            placeholder: 'Password'
        }
    ]

    const submitForm = () => {
        submit(ConfirmablePasswordController.store(), {
            onFinish: () => {
                form.reset()
            }
        })
    }
</script>

<template>
    <AuthLayout title="Confirm your password" description="This is a secure area of the application. Please confirm your password before continuing.">
        <Head title="Confirm password" />

        <BaseFormsBaseFormRenderer
            :model="form as unknown as Record<string, unknown>"
            :fields="fields"
            :errors="form.errors"
            :processing="form.processing"
            submit-label="Confirm Password"
            @submit="submitForm"
        >
            <template #actions>
                <BaseButton type="submit" full-width :loading="form.processing" label="Confirm Password" />
            </template>
        </BaseFormsBaseFormRenderer>
    </AuthLayout>
</template>
