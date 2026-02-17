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
    const formatRole = (value: string): string => value.charAt(0).toUpperCase() + value.slice(1)
</script>

<template>
    <UiCard>
        <UiCardContent class="p-0">
            <div class="grid gap-3 p-3 sm:p-4 md:hidden">
                <article v-for="user in users" :key="user.id" class="rounded-lg border border-border/70 bg-card p-3 shadow-xs">
                    <div class="flex items-start gap-3">
                        <div class="flex min-w-0 flex-1 items-center gap-2">
                            <UserInfo :user="user" />
                        </div>
                        <UsersActionsDropdown
                            :user="user"
                            :current-user-id="currentUserId"
                            @edit="emit('edit', $event)"
                            @delete="emit('delete', $event)"
                        />
                    </div>

                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="grid gap-1">
                            <dt class="text-xs tracking-wide text-muted-foreground uppercase">Role</dt>
                            <dd>{{ formatRole(user.role) }}</dd>
                        </div>
                        <div class="grid gap-1">
                            <dt class="text-xs tracking-wide text-muted-foreground uppercase">Email</dt>
                            <dd class="text-sm break-all">{{ user.email }}</dd>
                        </div>
                        <div class="grid gap-1">
                            <dt class="text-xs tracking-wide text-muted-foreground uppercase">Created</dt>
                            <dd>{{ formatDate(user.created_at) }}</dd>
                        </div>
                    </dl>
                </article>

                <div v-if="users.length === 0" class="rounded-lg border border-dashed border-border/70 p-6 text-center text-sm text-muted-foreground">
                    No users found.
                </div>
            </div>

            <div class="hidden md:block">
                <div class="relative overflow-x-auto">
                    <table class="w-full min-w-[700px]">
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
                                <td class="p-4">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <UserInfo :user="user" />
                                    </div>
                                </td>
                                <td class="p-4 capitalize">{{ formatRole(user.role) }}</td>
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
                            <tr v-if="users.length === 0">
                                <td colspan="5" class="p-8 text-center text-muted-foreground">No users found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </UiCardContent>
    </UiCard>
</template>
