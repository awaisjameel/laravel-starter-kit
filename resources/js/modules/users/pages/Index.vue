<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import appRoutes from '@/routes/app'
    import { UsersPageProps, type BreadcrumbItem, type User } from '@/types'
    import { Plus } from 'lucide-vue-next'
    import { useAppPage } from '../../../composables/useAppPage'

    const userSortColumns = ['name', 'email', 'role', 'created_at'] as const
    type UserSortColumn = 'name' | 'email' | 'role' | 'created_at'

    const page = useAppPage()
    const props = defineProps<UsersPageProps>()
    const currentUserId = computed(() => page.props.auth.user?.id ?? 0)

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Users',
            href: appRoutes.admin.users.index.url()
        }
    ]

    const isUserDialogOpen = ref(false)
    const isDeleteDialogOpen = ref(false)
    const selectedUser = ref<User | null>(null)
    const userDialogMode = ref<'create' | 'edit'>('create')

    const locationSearch = (() => {
        try {
            return new URL(page.props.ziggy.location).search
        } catch {
            return ''
        }
    })()

    const initialQuery = resolveServerTableInitialQuery<UserSortColumn>({
        locationSearch,
        fallback: {
            page: props.users.current_page,
            perPage: props.users.per_page,
            search: undefined,
            sortBy: 'created_at',
            sortDirection: 'desc'
        },
        allowedSortBy: userSortColumns,
        defaultSortBy: 'created_at',
        defaultSortDirection: 'desc'
    })

    const { query, searchValue, setPage, setPerPage, setSort } = useServerDataTable<UserSortColumn>({
        endpoint: UserController.index,
        initialQuery,
        debounceMs: 300
    })

    const openCreateUserDialog = (): void => {
        userDialogMode.value = 'create'
        selectedUser.value = null
        isUserDialogOpen.value = true
    }

    const onEditUser = (user: User): void => {
        selectedUser.value = user
        userDialogMode.value = 'edit'
        isUserDialogOpen.value = true
    }

    const onUserSaved = (): void => {
        isUserDialogOpen.value = false
    }

    const onDeleteUser = (user: User): void => {
        selectedUser.value = user
        isDeleteDialogOpen.value = true
    }

    const onUserDeleted = (): void => {
        isDeleteDialogOpen.value = false
    }
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full min-w-0 flex-1 flex-col gap-4 rounded-xl p-3 sm:p-4">
            <div class="mt-2 flex flex-col gap-4 sm:mt-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Heading title="Users" description="Manage user accounts" />
                    <BaseButton class="w-full sm:w-auto" label="Add User" :icon-left="Plus" @click="openCreateUserDialog" />
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
                    @sort="setSort($event)"
                />

                <BaseTableBaseDataTablePagination
                    :current-page="props.users.current_page"
                    :total-pages="props.users.last_page"
                    :items-per-page="props.users.per_page"
                    :total-items="props.users.total"
                    @page-change="setPage($event)"
                />
            </div>

            <UsersUserFormDialog
                v-if="isUserDialogOpen"
                :open="isUserDialogOpen"
                :mode="userDialogMode"
                :user="selectedUser"
                @update:open="isUserDialogOpen = $event"
                @created="onUserSaved"
                @updated="onUserSaved"
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
