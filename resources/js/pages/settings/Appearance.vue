<script setup lang="ts">
    import { type BreadcrumbItem } from '@/types'

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Appearance settings',
            href: route('appearance')
        }
    ]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
