<script setup lang="ts">
    import type { FormFieldSchema, FormSectionSchema } from '@/types/base-ui'

    interface Props {
        model: Record<string, unknown>
        fields?: Array<FormFieldSchema<Record<string, unknown>>>
        sections?: Array<FormSectionSchema<Record<string, unknown>>>
        errors?: Record<string, string | undefined>
        processing?: boolean
        submitLabel?: string
        cancelLabel?: string
        showCancel?: boolean
    }

    const props = withDefaults(defineProps<Props>(), {
        fields: () => [],
        sections: () => [],
        errors: () => ({}),
        processing: false,
        submitLabel: 'Save',
        cancelLabel: 'Cancel',
        showCancel: false
    })

    const emit = defineEmits<{
        submit: []
        cancel: []
    }>()

    const normalizedSections = computed<Array<FormSectionSchema<Record<string, unknown>>>>(() => {
        if (props.sections.length > 0) {
            return props.sections
        }

        if (props.fields.length > 0) {
            return [
                {
                    key: 'default',
                    fields: props.fields
                }
            ]
        }

        return []
    })

    const setModelFieldValue = (model: Record<string, unknown>, fieldName: string, value: unknown): void => {
        model[fieldName] = value
    }

    const setValue = (fieldName: string, value: unknown): void => {
        setModelFieldValue(props.model, fieldName, value)
    }
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <div class="space-y-6">
            <section v-for="section in normalizedSections" :key="section.key" class="space-y-4">
                <div v-if="section.title !== undefined || section.description !== undefined" class="space-y-1">
                    <h3 v-if="section.title !== undefined" class="text-base font-semibold">{{ section.title }}</h3>
                    <p v-if="section.description !== undefined" class="text-sm text-muted-foreground">{{ section.description }}</p>
                </div>

                <div class="grid gap-4">
                    <BaseFormsBaseInputField
                        v-for="field in section.fields"
                        :id="field.name"
                        :key="field.name"
                        :field="field"
                        :model-value="props.model[field.name]"
                        :error="props.errors[field.name]"
                        @update:model-value="setValue(field.name, $event)"
                    />
                </div>
            </section>
        </div>

        <slot name="actions">
            <BaseFormsBaseFormActions
                :processing="props.processing"
                :submit-label="props.submitLabel"
                :cancel-label="props.cancelLabel"
                :show-cancel="props.showCancel"
                @cancel="emit('cancel')"
            />
        </slot>
    </form>
</template>
