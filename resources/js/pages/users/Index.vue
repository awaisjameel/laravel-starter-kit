<script setup lang="ts">
    import { UsersPageProps, type BreadcrumbItem, type User } from '@/types'

    const page = usePage()
    const props = defineProps<UsersPageProps>()

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Users',
            href: '/users'
        }
    ]

    const isCreateDialogOpen = ref(false)
    const isEditDialogOpen = ref(false)
    const isDeleteDialogOpen = ref(false)
    const selectedUser = ref<User | null>(props.users.data[0] ?? null)

    const currentPage = computed(() => Number(props.users.current_page) || 1)
    const totalPages = computed(() => Number(props.users.last_page) || 1)
    const itemsPerPage = computed(() => Number(props.users.per_page) || 10)
    const totalItems = computed(() => Number(props.users.total) || 0)

    const pageNumbers = computed(() => {
        const pages: number[] = []
        const siblingCount = 2
        const validTotalPages = Math.max(1, totalPages.value)
        const validCurrentPage = Math.max(1, Math.min(currentPage.value, validTotalPages))
        const start = Math.max(1, validCurrentPage - siblingCount)
        const end = Math.min(validTotalPages, validCurrentPage + siblingCount)

        for (let i = start; i <= end; i++) {
            pages.push(i)
        }

        return pages.length > 0 ? pages : [1]
    })

    const onPageChange = (page: number) => {
        if (page !== currentPage.value && page >= 1 && page <= totalPages.value) {
            router.get('/users', { page, perPage: itemsPerPage.value }, { preserveState: true })
        }
    }

    const onCreateUser = async () => {
        isCreateDialogOpen.value = false
    }

    const onEditUser = (user: User) => {
        selectedUser.value = user
        isEditDialogOpen.value = true
    }

    const onUpdateUser = async () => {
        isEditDialogOpen.value = false
    }

    const onDeleteUser = (user: User) => {
        selectedUser.value = user
        isDeleteDialogOpen.value = true
    }

    const onUserDeleted = async () => {
        isDeleteDialogOpen.value = false
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="mt-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <Heading title="Users" description="Manage user accounts" />
                    <UiButton @click="isCreateDialogOpen = true">
                        <Icon-mdi-plus />
                        Add User
                    </UiButton>
                </div>

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
                                        <tr v-for="item in props.users.data" :key="item.id" class="border-b transition-colors hover:bg-muted/50">
                                            <td class="flex flex-row items-center justify-center gap-2 p-4">
                                                <UserInfo :user="item" />
                                            </td>
                                            <td class="p-4">{{ item.email }}</td>
                                            <td class="p-4 capitalize">{{ item.role }}</td>
                                            <td class="p-4">{{ new Date(item.created_at).toLocaleDateString() }}</td>
                                            <td class="p-4 text-right">
                                                <UiDropdownMenu>
                                                    <UiDropdownMenuTrigger as-child>
                                                        <UiButton variant="ghost" class="h-8 w-8 p-0">
                                                            <span class="sr-only">Open menu</span>
                                                            <Icon-mdi-dots-vertical class="h-4 w-4" />
                                                        </UiButton>
                                                    </UiDropdownMenuTrigger>
                                                    <UiDropdownMenuContent align="end">
                                                        <UiDropdownMenuItem @click="onEditUser(item)"> Edit </UiDropdownMenuItem>
                                                        <UiDropdownMenuItem
                                                            v-if="page.props.auth.user.id !== item.id"
                                                            @click="onDeleteUser(item)"
                                                            class="text-red-600 focus:text-red-600"
                                                        >
                                                            Delete
                                                        </UiDropdownMenuItem>
                                                    </UiDropdownMenuContent>
                                                </UiDropdownMenu>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </UiCardContent>
                </UiCard>

                <div v-if="totalPages > 1" class="mt-4 flex flex-row items-center justify-between">
                    <UiPagination
                        :total="totalItems"
                        :items-per-page="itemsPerPage"
                        :page="currentPage"
                        :sibling-count="2"
                        show-edges
                        @update:page="onPageChange"
                    >
                        <UiPaginationContent>
                            <!-- <UiPaginationFirst @click="onPageChange(1)" /> -->
                            <UiPaginationPrevious @click="onPageChange(currentPage - 1)" :disabled="currentPage === 1">
                                <Icon-mdi-chevron-left class="h-4 w-4" />
                            </UiPaginationPrevious>
                            <template v-for="(item, index) in pageNumbers" :key="index">
                                <UiPaginationItem :value="item" as-child>
                                    <UiButton
                                        class="h-10 w-10 p-0"
                                        :variant="item === currentPage ? 'outline' : 'ghost'"
                                        :aria-current="item === currentPage ? 'page' : undefined"
                                        :disabled="item === currentPage"
                                        @click="onPageChange(item)"
                                    >
                                        {{ item }}
                                    </UiButton>
                                </UiPaginationItem>
                            </template>
                            <UiPaginationNext @click="onPageChange(currentPage + 1)" :disabled="currentPage === totalPages">
                                <Icon-mdi-chevron-right class="h-4 w-4" />
                            </UiPaginationNext>
                            <!-- <UiPaginationLast @click="onPageChange(totalPages)" /> -->
                        </UiPaginationContent>
                    </UiPagination>
                </div>
            </div>

            <UsersCreateUserDialog
                v-if="isCreateDialogOpen"
                :open="isCreateDialogOpen"
                @update:open="isCreateDialogOpen = $event"
                @created="onCreateUser"
            />

            <UsersEditUserDialog
                v-if="isEditDialogOpen && selectedUser"
                :open="isEditDialogOpen"
                :user="selectedUser"
                @update:open="isEditDialogOpen = $event"
                @updated="onUpdateUser"
            />

            <UsersDeleteUserDialog
                v-if="isDeleteDialogOpen && selectedUser"
                :open="isDeleteDialogOpen"
                :user="selectedUser"
                @update:open="isDeleteDialogOpen = $event"
                @deleted="onUserDeleted"
            />
        </div>
    </AppLayout>
</template>
