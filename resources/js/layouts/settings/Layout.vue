<script setup lang="ts">
    import { type NavItem } from '@/types'

    const sidebarNavItems: NavItem[] = [
        {
            title: 'Profile',
            href: route('app.settings.profile.edit')
        },
        {
            title: 'Password',
            href: route('app.settings.password.edit')
        },
        {
            title: 'Appearance',
            href: route('app.settings.appearance')
        }
    ]

    const page = usePage()

    const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : ''
</script>

<template>
    <div class="px-4 py-6 sm:px-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col gap-8 lg:flex-row lg:gap-12">
            <aside class="w-full lg:w-48">
                <nav class="grid gap-1 sm:grid-cols-2 lg:grid-cols-1">
                    <UiButton
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </UiButton>
                </nav>
            </aside>

            <UiSeparator class="lg:hidden" />

            <div class="flex-1">
                <section class="grid max-w-xl gap-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
