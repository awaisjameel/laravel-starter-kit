<script setup lang="ts">
    /**
     * Reusable form field component that wraps label, input, and error display.
     * Follows the project's form patterns with grid gap-2 container.
     */
    defineProps<{
        label: string
        id: string
        error?: string
        type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url'
        modelValue?: string | number
        placeholder?: string
        required?: boolean
        disabled?: boolean
        autofocus?: boolean
        autocomplete?: string
        tabindex?: number
    }>()

    defineEmits<{
        'update:modelValue': [value: string | number]
    }>()
</script>

<template>
    <div class="grid gap-2">
        <UiLabel :for="id">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
        </UiLabel>
        <UiInput
            :id="id"
            :type="type ?? 'text'"
            :value="modelValue"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :autofocus="autofocus"
            :autocomplete="autocomplete"
            :tabindex="tabindex"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
        <InputError :message="error" />
    </div>
</template>
