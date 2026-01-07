<script setup lang="ts">
import users from '@/routes/users';
import { UserRole } from '@/types/app-data';

const emit = defineEmits(['update:open', 'created']);

defineProps<{
    open: boolean;
}>();

const rolesList = getEnumOptions(UserRole);

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
});
</script>

<template>
    <UiDialog :open="open" @update:open="emit('update:open', $event)">
        <UiDialogContent class="sm:max-w-[425px]">
            <UiDialogHeader>
                <UiDialogTitle>Create User</UiDialogTitle>
                <UiDialogDescription> Add a new user to the system </UiDialogDescription>
            </UiDialogHeader>

            <form @submit.prevent="form.submit(users.store(), { onSuccess: () => emit('created') })">
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <UiLabel for="name">Name</UiLabel>
                        <UiInput id="name" v-model="form.name" placeholder="Enter name" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <UiLabel for="email">Email</UiLabel>
                        <UiInput id="email" type="email" v-model="form.email" placeholder="Enter email" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="relative grid gap-2">
                        <UiLabel for="role">Role</UiLabel>
                        <UiSelect id="role" v-model="form.role">
                            <UiSelectTrigger class="w-full">
                                <UiSelectValue placeholder="Select a role" />
                            </UiSelectTrigger>
                            <UiSelectContent>
                                <UiSelectGroup>
                                    <UiSelectLabel>Roles</UiSelectLabel>
                                    <UiSelectItem v-for="(role, index) in rolesList" :key="index" :value="role.value">{{ role.label }}</UiSelectItem>
                                </UiSelectGroup>
                            </UiSelectContent>
                        </UiSelect>
                        <InputError :message="form.errors.role" />
                    </div>

                    <div class="grid gap-2">
                        <UiLabel for="password">Password</UiLabel>
                        <UiInput id="password" type="password" v-model="form.password" placeholder="Enter password" />
                        <InputError :message="form.errors.password" />
                    </div>
                </div>
                <UiDialogFooter>
                    <UiButton type="button" variant="ghost" @click="$emit('update:open', false)">Cancel</UiButton>
                    <UiButton type="submit" :disabled="form.processing">
                        <Icon-mdi-loading v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Create User
                    </UiButton>
                </UiDialogFooter>
            </form>
        </UiDialogContent>
    </UiDialog>
</template>
