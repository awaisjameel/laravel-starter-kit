<script setup lang="ts">
    import { type BreadcrumbItem } from '@/types'

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Password settings',
            href: '/settings/password'
        }
    ]

    const passwordInput = ref<HTMLInputElement | null>(null)
    const currentPasswordInput = ref<HTMLInputElement | null>(null)

    const form = useForm({
        current_password: '',
        password: '',
        password_confirmation: ''
    })

    const updatePassword = () => {
        form.put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
            onError: (errors: Record<string, string>) => {
                if (errors.password) {
                    form.reset('password', 'password_confirmation')
                    if (passwordInput.value instanceof HTMLInputElement) {
                        passwordInput.value.focus()
                    }
                }

                if (errors.current_password) {
                    form.reset('current_password')
                    if (currentPasswordInput.value instanceof HTMLInputElement) {
                        currentPasswordInput.value.focus()
                    }
                }
            }
        })
    }
</script>

<template>
    <AppFunnelLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Update password" description="Ensure your account is using a long, random password to stay secure" />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <UiLabel for="current_password">Current password</UiLabel>
                        <UiInput
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            placeholder="Current password"
                        />
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <UiLabel for="password">New password</UiLabel>
                        <UiInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="New password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <UiLabel for="password_confirmation">Confirm password</UiLabel>
                        <UiInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <UiButton :disabled="form.processing">Save password</UiButton>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppFunnelLayout>
</template>
