<script setup lang="ts">
    const theme = appTheme

    interface Props {
        id: string
        label: string
        description?: string
        error?: string
        required?: boolean
        hideLabel?: boolean
    }

    const props = withDefaults(defineProps<Props>(), {
        required: false,
        hideLabel: false
    })
</script>

<template>
    <div class="grid gap-2">
        <UiLabel v-if="!props.hideLabel" :for="props.id">
            {{ props.label }}
            <span v-if="props.required" class="text-destructive">*</span>
        </UiLabel>
        <slot />
        <p v-if="props.description !== undefined && props.description !== ''" :class="theme.field.description">
            {{ props.description }}
        </p>
        <InputError :message="props.error" />
    </div>
</template>
