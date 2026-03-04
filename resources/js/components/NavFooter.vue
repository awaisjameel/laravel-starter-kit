<script setup lang="ts">
    import { type NavItem } from '@/types'

    interface Props {
        items: NavItem[]
        class?: string
    }

    defineProps<Props>()
</script>

<template>
    <UiSidebarGroup :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
        <UiSidebarGroupContent>
            <UiSidebarMenu>
                <UiSidebarMenuItem v-for="item in items" :key="item.title">
                    <UiSidebarMenuButton
                        class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100"
                        :is-active="item.isActive"
                        as-child
                    >
                        <a v-if="item.external" :href="item.href" target="_blank" rel="noopener noreferrer">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </a>
                        <Link v-else :href="item.href" :aria-current="item.isActive ? 'page' : undefined">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </UiSidebarMenuButton>
                </UiSidebarMenuItem>
            </UiSidebarMenu>
        </UiSidebarGroupContent>
    </UiSidebarGroup>
</template>
