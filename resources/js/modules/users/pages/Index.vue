<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import { UsersPageProps, type BreadcrumbItem, type User } from '@/types'
    import { Plus } from 'lucide-vue-next'

    type UserSortColumn = 'name' | 'email' | 'role' | 'created_at'

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

    const getQueryValue = (key: string): string | undefined => {
        if (typeof window === 'undefined') {
            return undefined
        }

        const params = new URLSearchParams(window.location.search)
        const value = params.get(key)
        return value === null || value === '' ? undefined : value
    }

    const initialQuery = {
        page: Number(getQueryValue('page') ?? props.users.current_page) || 1,
        perPage: Number(getQueryValue('perPage') ?? props.users.per_page) || 10,
        search: getQueryValue('search'),
        sortBy: (getQueryValue('sortBy') as UserSortColumn | undefined) ?? 'created_at',
        sortDirection: (getQueryValue('sortDirection') as 'asc' | 'desc' | undefined) ?? 'desc'
    }

    const { query, searchValue, setPage, setPerPage, setSort } = useServerDataTable<UserSortColumn>({
        endpoint: UserController.index,
        initialQuery,
        debounceMs: 300
    })

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
                    <BaseButton class="w-full sm:w-auto" label="Add User" :icon-left="Plus" @click="isCreateDialogOpen = true" />
                </div>

                <BaseTableBaseDataTableToolbar
                    :search-value="searchValue"
                    :per-page="query.perPage"
                    search-placeholder="Search users..."
                    @update:search-value="searchValue = $event"
                    @update:per-page="setPerPage($event)"
                />

                <UsersTable
                    :users="props.users.data"
                    :current-user-id="currentUserId"
                    :sort-by="query.sortBy"
                    :sort-direction="query.sortDirection"
                    @edit="onEditUser"
                    @delete="onDeleteUser"
                    @sort="setSort($event as UserSortColumn)"
                />

                <UsersPagination
                    :current-page="props.users.current_page"
                    :total-pages="props.users.last_page"
                    :items-per-page="props.users.per_page"
                    :total-items="props.users.total"
                    @page-change="setPage($event)"
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
