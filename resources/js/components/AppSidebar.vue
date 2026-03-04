<script setup lang="ts">
    import appRoutes from '@/routes/app'
    import AppLogo from './AppLogo.vue'

    const { dashboardPrimaryItems, dashboardFooterItems } = useNavigation()
</script>

<template>
    <UiSidebar collapsible="icon" variant="inset">
        <UiSidebarHeader>
            <UiSidebarMenu>
                <UiSidebarMenuItem>
                    <UiSidebarMenuButton size="lg" as-child>
                        <Link :href="appRoutes.dashboard.url()">
                            <AppLogo />
                        </Link>
                    </UiSidebarMenuButton>
                </UiSidebarMenuItem>
            </UiSidebarMenu>
        </UiSidebarHeader>

        <UiSidebarContent>
            <NavMain :items="dashboardPrimaryItems" />
        </UiSidebarContent>

        <UiSidebarFooter>
            <NavFooter :items="dashboardFooterItems" />
            <NavUser />
        </UiSidebarFooter>
    </UiSidebar>
    <slot />
</template>
