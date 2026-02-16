<script setup lang="ts">
    import type { User } from '@/types'

    interface Props {
        currentUserId: number
        users: User[]
    }

    defineProps<Props>()

    const emit = defineEmits<{
        delete: [user: User]
        edit: [user: User]
    }>()

    const formatDate = (value: string): string => new Date(value).toLocaleDateString()
</script>

<template>
    <UiCard>
        <UiCardContent class="p-0">
            <div class="relative min-h-[400px]">
                <div class="relative">
                    <table class="w-full">
                        <thead class="border-b">
                            <tr class="hover:bg-transparent">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Role</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Email</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Created At</th>
                                <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="flex flex-row items-center justify-center gap-2 p-4">
                                    <UserInfo :user="user" />
                                </td>
                                <td class="p-4 capitalize">{{ user.role }}</td>
                                <td class="p-4">{{ user.email }}</td>
                                <td class="p-4">{{ formatDate(user.created_at) }}</td>
                                <td class="p-4 text-right">
                                    <UsersActionsDropdown
                                        :user="user"
                                        :current-user-id="currentUserId"
                                        @edit="emit('edit', $event)"
                                        @delete="emit('delete', $event)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </UiCardContent>
    </UiCard>
</template>
