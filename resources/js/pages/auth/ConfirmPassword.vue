<script setup lang="ts">
    import { LoaderCircle } from 'lucide-vue-next'

    const form = useForm({
        password: ''
    })

    const submit = () => {
        form.post(route('password.confirm'), {
            onFinish: () => {
                form.reset()
            }
        })
    }
</script>

<template>
    <AuthLayout title="Confirm your password" description="This is a secure area of the application. Please confirm your password before continuing.">
        <Head title="Confirm password" />

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <UiLabel htmlFor="password">Password</UiLabel>
                    <UiInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        autofocus
                    />

                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center">
                    <UiButton class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Confirm Password
                    </UiButton>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
