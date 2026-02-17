<script setup lang="ts">
    import { FileQuestion, FolderOpen, Inbox, Search } from 'lucide-vue-next'

    export interface EmptyStateProps {
        title?: string
        description?: string
        icon?: 'inbox' | 'search' | 'folder' | 'question'
        actionText?: string
    }

    withDefaults(defineProps<EmptyStateProps>(), {
        title: 'No data found',
        description: 'There are no items to display.',
        icon: 'inbox'
    })

    const emit = defineEmits<{
        (e: 'action'): void
    }>()

    const iconMap = {
        inbox: Inbox,
        search: Search,
        folder: FolderOpen,
        question: FileQuestion
    }
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
            <component :is="iconMap[icon]" class="h-8 w-8 text-gray-400 dark:text-gray-500" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ title }}
        </h3>
        <p class="mt-2 max-w-sm text-sm text-gray-500 dark:text-gray-400">
            {{ description }}
        </p>
        <button
            v-if="actionText"
            type="button"
            class="mt-6 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-primary/90 focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none"
            @click="emit('action')"
        >
            {{ actionText }}
        </button>
        <slot name="action" />
    </div>
</template>
