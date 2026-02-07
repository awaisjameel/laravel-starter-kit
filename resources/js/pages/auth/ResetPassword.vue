<script setup lang="ts">
    interface Props {
        token: string
        email: string
    }

    const props = defineProps<Props>()

    const form = useForm({
        token: props.token,
        email: props.email,
        password: '',
        password_confirmation: ''
    })

    const submit = () => {
        form.post(route('password.store'), {
            onFinish: () => {
                form.reset('password', 'password_confirmation')
            }
        })
    }
</script>

<template>
    <AuthLayout title="Reset password" description="Please enter your new password below">
        <Head title="Reset password" />

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <UiLabel for="email">Email</UiLabel>
                    <UiInput id="email" type="email" name="email" autocomplete="email" v-model="form.email" class="mt-1 block w-full" readonly />
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <div class="grid gap-2">
                    <UiLabel for="password">Password</UiLabel>
                    <UiInput
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        v-model="form.password"
                        class="mt-1 block w-full"
                        autofocus
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <UiLabel for="password_confirmation"> Confirm Password </UiLabel>
                    <UiInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        class="mt-1 block w-full"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <UiButton type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Reset password
                </UiButton>
            </div>
        </form>
    </AuthLayout>
</template>
