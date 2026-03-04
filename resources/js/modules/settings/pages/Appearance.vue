<script setup lang="ts">
    import AppearanceTabs from '@/modules/settings/components/AppearanceTabs.vue'

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
