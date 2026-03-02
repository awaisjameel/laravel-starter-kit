<script setup lang="ts">
    import { useSidebar } from '@/components/ui/sidebar/utils'
    import { ChevronsUpDown } from 'lucide-vue-next'

    const page = usePage()
    const user = page.props.auth.user

    if (user === null) {
        throw new Error('Authenticated user is required for NavUser.')
    }

    const { isMobile, state } = useSidebar()
</script>

<template>
    <UiSidebarMenu>
        <UiSidebarMenuItem>
            <UiDropdownMenu>
                <UiDropdownMenuTrigger as-child>
                    <UiSidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                        <UserInfo :user="user" />
                        <ChevronsUpDown class="ml-auto size-4" />
                    </UiSidebarMenuButton>
                </UiDropdownMenuTrigger>
                <UiDropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : state === 'collapsed' ? 'left' : 'bottom'"
                    align="end"
                    :side-offset="4"
                >
                    <UserMenuContent :user="user" />
                </UiDropdownMenuContent>
            </UiDropdownMenu>
        </UiSidebarMenuItem>
    </UiSidebarMenu>
</template>
