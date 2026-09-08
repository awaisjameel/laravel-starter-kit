<script setup lang="ts">
    const theme = appTheme

    defineProps<{
        message?: string
    }>()
</script>

<template>
    <div v-show="message">
        <p :class="theme.field.error">
            {{ message }}
        </p>
    </div>
</template>
