<script setup lang="ts">
    import { Moon, Sun } from 'lucide-vue-next'

    const { appearance, updateAppearance } = useAppearance()

    const tabs = [
        { value: 'light', Icon: Sun, label: 'Light' },
        { value: 'dark', Icon: Moon, label: 'Dark' }
    ] as const
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg border border-border/70 bg-muted/50 p-1">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                appearance === value ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:bg-muted hover:text-foreground'
            ]"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
