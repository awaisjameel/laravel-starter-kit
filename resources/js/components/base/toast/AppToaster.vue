<script setup lang="ts">
    const { toasts, dismiss } = useToast()

    useFlashToasts()
</script>

<template>
    <UiToastProvider>
        <UiToast
            v-for="toast in toasts"
            :key="toast.id"
            :open="true"
            :variant="toast.variant"
            @update:open="(open: boolean) => !open && dismiss(toast.id)"
        >
            <div class="grid gap-1">
                <UiToastTitle>{{ toast.title }}</UiToastTitle>
                <UiToastDescription v-if="toast.description !== undefined">{{ toast.description }}</UiToastDescription>
            </div>
            <UiToastClose />
        </UiToast>
        <UiToastViewport />
    </UiToastProvider>
</template>
