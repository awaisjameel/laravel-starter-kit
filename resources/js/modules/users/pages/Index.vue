<script setup lang="ts">
    import UserController from '@/actions/App/Modules/Users/Http/Controllers/UserController'
    import { SortDirection, UserSortBy, type UsersIndexPageData, type UserViewData } from '@/types/app-data'
    import { Plus } from 'lucide-vue-next'

    type UserSortColumn = `${UserSortBy}`

    const userSortColumns = [UserSortBy.Name, UserSortBy.Email, UserSortBy.Role, UserSortBy.CreatedAt] as const

    const page = useAppPage()
    const authenticatedUser = useAuthUser({ required: true, context: 'UsersIndexPage' })
    const props = defineProps<UsersIndexPageData>()
    const currentUserId = computed(() => authenticatedUser.value.id)

    const breadcrumbs = buildUsersBreadcrumbs()

    const isUserDialogOpen = ref(false)
    const isDeleteDialogOpen = ref(false)
    const selectedUser = ref<UserViewData | null>(null)
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
            sortBy: UserSortBy.CreatedAt,
            sortDirection: SortDirection.Desc
        },
        allowedSortBy: userSortColumns,
        defaultSortBy: UserSortBy.CreatedAt,
        defaultSortDirection: SortDirection.Desc
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

    const onEditUser = (user: UserViewData): void => {
        selectedUser.value = user
        userDialogMode.value = 'edit'
        isUserDialogOpen.value = true
    }

    const onUserSaved = (): void => {
        isUserDialogOpen.value = false
    }

    const onDeleteUser = (user: UserViewData): void => {
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
