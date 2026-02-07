<script setup lang="ts">
    const passwordInput = ref<HTMLInputElement | null>(null)

    const form = useForm({
        password: ''
    })

    const deleteUser = (e: Event) => {
        e.preventDefault()

        form.delete(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.value?.focus(),
            onFinish: () => form.reset()
        })
    }

    const closeModal = () => {
        form.clearErrors()
        form.reset()
    }
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall title="Delete account" description="Delete your account and all of its resources" />
        <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warning</p>
                <p class="text-sm">Please proceed with caution, this cannot be undone.</p>
            </div>
            <UiDialog>
                <UiDialogTrigger as-child>
                    <UiButton variant="destructive">Delete account</UiButton>
                </UiDialogTrigger>
                <UiDialogContent>
                    <form class="space-y-6" @submit="deleteUser">
                        <UiDialogHeader class="space-y-3">
                            <UiDialogTitle>Are you sure you want to delete your account?</UiDialogTitle>
                            <UiDialogDescription>
                                Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your
                                password to confirm you would like to permanently delete your account.
                            </UiDialogDescription>
                        </UiDialogHeader>

                        <div class="grid gap-2">
                            <UiLabel for="password" class="sr-only">Password</UiLabel>
                            <UiInput
                                id="password"
                                type="password"
                                name="password"
                                ref="passwordInput"
                                v-model="form.password"
                                placeholder="Password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <UiDialogFooter class="gap-2">
                            <UiDialogClose as-child>
                                <UiButton variant="secondary" @click="closeModal"> Cancel </UiButton>
                            </UiDialogClose>

                            <UiButton type="submit" variant="destructive" :disabled="form.processing"> Delete account </UiButton>
                        </UiDialogFooter>
                    </form>
                </UiDialogContent>
            </UiDialog>
        </div>
    </div>
</template>
