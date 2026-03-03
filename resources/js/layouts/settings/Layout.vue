<script setup lang="ts">
    import { buildSettingsNavItems } from '@/config/navigation'

    const sidebarNavItems = buildSettingsNavItems()

    const page = usePage()

    const normalizePath = (value: string): string => {
        const trimmed = value.replace(/\/+$/, '')
        return trimmed === '' ? '/' : trimmed
    }

    const currentPath = computed(() => {
        const location = page.props.ziggy?.location

        if (location === undefined || location === '') {
            return ''
        }

        return normalizePath(new URL(location).pathname)
    })

    const isActive = (href: string): boolean => {
        const baseLocation = page.props.ziggy?.location ?? 'http://localhost'
        const hrefPath = normalizePath(new URL(href, baseLocation).pathname)
        return currentPath.value === hrefPath
    }
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
                        :class="['w-full justify-start', { 'bg-muted font-medium text-foreground': isActive(item.href) }]"
                        as-child
                    >
                        <Link :href="item.href" :aria-current="isActive(item.href) ? 'page' : undefined">
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
