<script setup lang="ts">
    import { UsersPageProps, type BreadcrumbItem, type User } from '@/types'

    const page = usePage()
    const props = defineProps<UsersPageProps>()
    const currentUserId = computed(() => page.props.auth.user?.id ?? 0)

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Users',
            href: route('app.admin.users.index')
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
            router.get(route('app.admin.users.index'), { page, perPage: itemsPerPage.value }, { preserveState: true })
        }
    }

    const onCreateUser = () => {
        isCreateDialogOpen.value = false
    }

    const onEditUser = (user: User) => {
        selectedUser.value = user
        isEditDialogOpen.value = true
    }

    const onUpdateUser = () => {
        isEditDialogOpen.value = false
    }

    const onDeleteUser = (user: User) => {
        selectedUser.value = user
        isDeleteDialogOpen.value = true
    }

    const onUserDeleted = () => {
        isDeleteDialogOpen.value = false
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full min-w-0 flex-1 flex-col gap-4 rounded-xl p-3 sm:p-4">
            <div class="mt-2 flex flex-col gap-4 sm:mt-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Heading title="Users" description="Manage user accounts" />
                    <UiButton class="w-full sm:w-auto" @click="isCreateDialogOpen = true">
                        <Icon-mdi-plus />
                        Add User
                    </UiButton>
                </div>

                <UsersTable :users="props.users.data" :current-user-id="currentUserId" @edit="onEditUser" @delete="onDeleteUser" />

                <UsersPagination
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :items-per-page="itemsPerPage"
                    :total-items="totalItems"
                    :page-numbers="pageNumbers"
                    @page-change="onPageChange"
                />
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
