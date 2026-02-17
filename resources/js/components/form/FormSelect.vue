<script setup lang="ts">
    import type { SelectOption } from '@/types'

    /**
     * Reusable form select component that wraps label, select, and error display.
     * Follows the project's form patterns with grid gap-2 container.
     */
    defineProps<{
        label: string
        id: string
        options: SelectOption[]
        modelValue?: string
        placeholder?: string
        error?: string
        required?: boolean
        disabled?: boolean
    }>()

    const emit = defineEmits<{
        'update:modelValue': [value: string]
    }>()

    const handleUpdate = (value: unknown) => {
        if (typeof value === 'string') {
            emit('update:modelValue', value)
        }
    }
</script>

<template>
    <div class="grid gap-2">
        <UiLabel :for="id">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
        </UiLabel>
        <UiSelect :id="id" :model-value="modelValue" :disabled="disabled" @update:model-value="handleUpdate">
            <UiSelectTrigger class="w-full">
                <UiSelectValue :placeholder="placeholder ?? 'Select an option'" />
            </UiSelectTrigger>
            <UiSelectContent>
                <UiSelectGroup>
                    <UiSelectLabel v-if="label">{{ label }}</UiSelectLabel>
                    <UiSelectItem v-for="option in options" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </UiSelectItem>
                </UiSelectGroup>
            </UiSelectContent>
        </UiSelect>
        <InputError :message="error" />
    </div>
</template>
