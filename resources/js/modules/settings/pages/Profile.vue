<script setup lang="ts">
    import ProfileController from '@/actions/App/Modules/Settings/Http/Controllers/ProfileController'
    import { type BreadcrumbItem } from '@/types'
    import type { FormFieldSchema } from '@/types/base-ui'

    interface Props {
        mustVerifyEmail: boolean
        status?: string
    }

    defineProps<Props>()

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Profile settings',
            href: route('app.settings.profile.edit')
        }
    ]

    const page = usePage()
    const user = page.props.auth.user

    if (user === null) {
        throw new Error('Authenticated user is required for profile settings page.')
    }

    const { form, submit } = useResourceForm({
        name: user.name,
        email: user.email
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
            autocomplete: 'username',
            placeholder: 'Email address'
        }
    ]

    const submitForm = () => {
        submit(ProfileController.update(), {
            preserveScroll: true
        })
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <BaseFormsBaseFormRenderer
                    :model="form as unknown as Record<string, unknown>"
                    :fields="fields"
                    :errors="form.errors"
                    :processing="form.processing"
                    submit-label="Save"
                    @submit="submitForm"
                />

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Your email address is unverified.
                        <Link
                            :href="route('auth.verification.send')"
                            method="post"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Click here to resend the verification email.
                        </Link>
                    </p>

                    <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                </Transition>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
