<script setup lang="ts">
    import { Moon, Sun } from 'lucide-vue-next'

    const { appearance, updateAppearance } = useAppearance()

    const isDarkMode = computed({
        get: () => appearance.value === 'dark',
        set: (value: boolean) => {
            updateAppearance(value ? 'dark' : 'light')
        }
    })
</script>

<template>
    <div class="space-y-4">
        <UiTabs :model-value="appearance" @update:model-value="(value: 'light' | 'dark') => updateAppearance(value)" class="space-y-3">
            <UiTabsList class="w-full sm:w-auto">
                <UiTabsTrigger value="light" class="min-w-28">
                    <Sun class="mr-2 size-4" />
                    Light
                </UiTabsTrigger>
                <UiTabsTrigger value="dark" class="min-w-28">
                    <Moon class="mr-2 size-4" />
                    Dark
                </UiTabsTrigger>
            </UiTabsList>
        </UiTabs>

        <div class="flex items-center justify-between rounded-lg border p-3">
            <div>
                <p class="text-sm font-medium">Dark mode toggle</p>
                <p class="text-xs text-muted-foreground">Switch instantly between light and dark appearance.</p>
            </div>
            <UiSwitch v-model="isDarkMode" />
        </div>
    </div>
</template>
