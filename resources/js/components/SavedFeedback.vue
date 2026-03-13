<script setup lang="ts">
    defineProps<{
        show: boolean
        label?: string
    }>()
</script>

<template>
    <Transition
        enter-active-class="transition ease-in-out"
        enter-from-class="opacity-0"
        leave-active-class="transition ease-in-out"
        leave-to-class="opacity-0"
    >
        <p v-show="show" class="text-sm text-neutral-600">{{ label ?? 'Saved.' }}</p>
    </Transition>
</template>
