<script setup lang="ts">
    import PasswordController from '@/actions/App/Modules/Settings/Http/Controllers/PasswordController'
    import { type BreadcrumbItem } from '@/types'
    import type { FormFieldSchema } from '@/types/base-ui'

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Password settings',
            href: route('app.settings.password.edit')
        }
    ]

    const { form, submit } = useResourceForm({
        current_password: '',
        password: '',
        password_confirmation: ''
    })

    const fields: Array<FormFieldSchema<Record<string, unknown>>> = [
        {
            name: 'current_password',
            label: 'Current password',
            type: 'password',
            autocomplete: 'current-password',
            placeholder: 'Current password'
        },
        {
            name: 'password',
            label: 'New password',
            type: 'password',
            autocomplete: 'new-password',
            placeholder: 'New password'
        },
        {
            name: 'password_confirmation',
            label: 'Confirm password',
            type: 'password',
            autocomplete: 'new-password',
            placeholder: 'Confirm password'
        }
    ]

    const updatePassword = () => {
        submit(PasswordController.update(), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
            },
            onError: (errors) => {
                if (errors.password !== undefined) {
                    form.reset('password', 'password_confirmation')
                }

                if (errors.current_password !== undefined) {
                    form.reset('current_password')
                }
            }
        })
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Update password" description="Ensure your account is using a long, random password to stay secure" />

                <BaseFormsBaseFormRenderer
                    :model="form as unknown as Record<string, unknown>"
                    :fields="fields"
                    :errors="form.errors"
                    :processing="form.processing"
                    submit-label="Save password"
                    @submit="updatePassword"
                />

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                </Transition>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
