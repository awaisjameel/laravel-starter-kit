<script setup lang="ts">
    import { type NavItem } from '@/types'
    import { UserRole } from '@/types/app-data'
    import { LayoutGrid, Users } from 'lucide-vue-next'
    import AppLogo from './AppLogo.vue'

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid
        }
    ]

    const page = usePage()

    if (page.props.auth.user.role === UserRole.Admin) {
        mainNavItems.push({
            title: 'Users',
            href: '/users',
            icon: Users
        })
    }

    const footerNavItems: NavItem[] = []
</script>

<template>
    <UiSidebar collapsible="icon" variant="inset">
        <UiSidebarHeader>
            <UiSidebarMenu>
                <UiSidebarMenuItem>
                    <UiSidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </UiSidebarMenuButton>
                </UiSidebarMenuItem>
            </UiSidebarMenu>
        </UiSidebarHeader>

        <UiSidebarContent>
            <NavMain :items="mainNavItems" />
        </UiSidebarContent>

        <UiSidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </UiSidebarFooter>
    </UiSidebar>
    <slot />
</template>
