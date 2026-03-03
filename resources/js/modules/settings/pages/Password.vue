<script setup lang="ts">
    import PasswordController from '@/actions/App/Modules/Settings/Http/Controllers/PasswordController'
    import { buildPasswordFormFields, type PasswordFormValues } from '@/modules/settings/forms/password-form-schema'
    import { type BreadcrumbItem } from '@/types'

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Password settings',
            href: route('app.settings.password.edit')
        }
    ]

    const { form, submit } = useResourceForm<PasswordFormValues>({
        current_password: '',
        password: '',
        password_confirmation: ''
    })

    const fields = buildPasswordFormFields()

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
                    :model="form"
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
