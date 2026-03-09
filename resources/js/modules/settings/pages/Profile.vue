<script setup lang="ts">
    import ProfileController from '@/actions/App/Modules/Settings/Http/Controllers/ProfileController'
    import type { ProfilePageData } from '@/types/app-data'

    defineProps<ProfilePageData>()

    const breadcrumbs = buildSettingsProfileBreadcrumbs()
    const resendVerificationHref = authRoutes.verification.send.url()

    const user = useAuthUser({ required: true, context: 'profile settings page' })

    const { form, fields, submit } = useSchemaResourceForm<ProfileFormValues>(
        profileFormContract,
        createProfileFormDefaults({
            name: user.value.name,
            email: user.value.email
        })
    )

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
                    :model="form"
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
                            :href="resendVerificationHref"
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

            <SettingsDeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
