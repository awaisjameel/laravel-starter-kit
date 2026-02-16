<script setup lang="ts">
    import { type NavItem } from '@/types'

    defineProps<{
        items: NavItem[]
    }>()

    const page = usePage()

    const isActiveItem = (href: string) => page.url === href || page.url.startsWith(`${href}?`)
</script>

<template>
    <UiSidebarGroup class="px-2 py-0">
        <UiSidebarGroupLabel>Platform</UiSidebarGroupLabel>
        <UiSidebarMenu>
            <UiSidebarMenuItem v-for="item in items" :key="item.title">
                <UiSidebarMenuButton as-child :is-active="isActiveItem(item.href)" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </UiSidebarMenuButton>
            </UiSidebarMenuItem>
        </UiSidebarMenu>
    </UiSidebarGroup>
</template>
