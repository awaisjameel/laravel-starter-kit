<script setup lang="ts">
    import type { User } from '@/types'
    import { LogOut, Settings } from 'lucide-vue-next'

    interface Props {
        user: User
    }

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
            <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </UiDropdownMenuItem>
    </UiDropdownMenuGroup>
    <UiDropdownMenuSeparator />
    <UiDropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </UiDropdownMenuItem>
</template>
