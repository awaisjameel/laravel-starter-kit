<script setup lang="ts">
    import { buildSettingsAppearanceBreadcrumbs } from '@/config/breadcrumbs'

    const breadcrumbItems = buildSettingsAppearanceBreadcrumbs()
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
