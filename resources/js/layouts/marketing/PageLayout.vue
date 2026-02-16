<script setup lang="ts"></script>

<template>
    <div class="relative min-h-svh overflow-hidden bg-background text-foreground">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_var(--color-muted)_0%,_transparent_60%)] opacity-70 dark:opacity-25" />
        <div class="relative mx-auto flex w-full max-w-7xl flex-col px-6 py-8 lg:px-8">
            <slot />
        </div>
    </div>
</template>
