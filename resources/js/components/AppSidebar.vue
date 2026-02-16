<script setup lang="ts">
    import AppLogo from './AppLogo.vue'

    const { dashboardPrimaryItems, dashboardFooterItems } = useNavigation()
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
            <NavMain :items="dashboardPrimaryItems" />
        </UiSidebarContent>

        <UiSidebarFooter>
            <NavFooter :items="dashboardFooterItems" />
            <NavUser />
        </UiSidebarFooter>
    </UiSidebar>
    <slot />
</template>
