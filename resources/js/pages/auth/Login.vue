<script setup lang="ts">
    import { LoaderCircle } from 'lucide-vue-next'

    defineProps<{
        status?: string
        canResetPassword: boolean
    }>()

    const form = useForm({
        email: '',
        password: '',
        remember: false
    })

    const submit = () => {
        form.post(route('login'), {
            onFinish: () => form.reset('password')
        })
    }
</script>

<template>
    <AuthLayout title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <UiLabel for="email">Email address</UiLabel>
                    <UiInput
                        id="email"
                        type="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <UiLabel for="password">Password</UiLabel>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                            Forgot password?
                        </TextLink>
                    </div>
                    <UiInput
                        id="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <UiLabel for="remember" class="flex items-center space-x-3">
                        <UiCheckbox id="remember" v-model="form.remember" :tabindex="3" />
                        <span>Remember me</span>
                    </UiLabel>
                </div>

                <UiButton type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Log in
                </UiButton>
            </div>
        </form>
    </AuthLayout>
</template>
