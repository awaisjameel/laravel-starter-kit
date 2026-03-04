<script setup lang="ts">
    import type { UserViewData } from '@/types/app-data'
    import { LogOut, Settings } from 'lucide-vue-next'

    interface Props {
        user: UserViewData
    }

    const settingsProfileHref = appRoutes.settings.profile.edit.url()
    const logoutHref = authRoutes.logout.url()

    const handleLogout = () => {
        router.flushAll()
    }

    defineProps<Props>()
</script>

<template>
    <UiDropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </UiDropdownMenuLabel>
    <UiDropdownMenuSeparator />
    <UiDropdownMenuGroup>
        <UiDropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="settingsProfileHref" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </UiDropdownMenuItem>
    </UiDropdownMenuGroup>
    <UiDropdownMenuSeparator />
    <UiDropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="logoutHref" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </UiDropdownMenuItem>
</template>
