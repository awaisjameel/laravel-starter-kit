<script setup lang="ts">
    import type { User } from '@/types'

    interface Props {
        currentUserId: number
        user: User
    }

    defineProps<Props>()

    const emit = defineEmits<{
        delete: [user: User]
        edit: [user: User]
    }>()
</script>

<template>
    <UiDropdownMenu>
        <UiDropdownMenuTrigger as-child>
            <UiButton variant="ghost" class="h-8 w-8 p-0">
                <span class="sr-only">Open menu</span>
                <Icon-mdi-dots-vertical class="h-4 w-4" />
            </UiButton>
        </UiDropdownMenuTrigger>
        <UiDropdownMenuContent align="end">
            <UiDropdownMenuItem @click="emit('edit', user)">Edit</UiDropdownMenuItem>
            <UiDropdownMenuItem v-if="currentUserId !== user.id" @click="emit('delete', user)" class="text-destructive focus:text-destructive">
                Delete
            </UiDropdownMenuItem>
        </UiDropdownMenuContent>
    </UiDropdownMenu>
</template>
