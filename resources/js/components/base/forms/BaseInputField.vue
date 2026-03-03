<script setup lang="ts" generic="TForm extends object">
    import type { FormFieldSchema, FormOption } from '@/types/base-ui'

    interface Props {
        id: string
        field: FormFieldSchema<TForm>
        modelValue: unknown
        error?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        error: undefined
    })

    const emit = defineEmits<{
        'update:modelValue': [value: unknown]
    }>()

    const resolvedOptions = computed<FormOption[]>(() => props.field.options ?? [])

    const onFileChange = (event: Event): void => {
        const target = event.target

        if (!(target instanceof HTMLInputElement)) {
            return
        }

        const files = target.files

        if (files === null) {
            emit('update:modelValue', props.field.multiple ? [] : null)
            return
        }

        if (props.field.multiple) {
            emit('update:modelValue', Array.from(files))
            return
        }

        emit('update:modelValue', files.item(0))
    }

    const onMultiSelectChange = (event: Event): void => {
        const target = event.target

        if (!(target instanceof HTMLSelectElement)) {
            return
        }

        const values = Array.from(target.selectedOptions).map((option) => option.value)
        emit('update:modelValue', values)
    }

    const toBoolean = (value: unknown): boolean => value === true
</script>

<template>
    <BaseFormsBaseFieldShell
        :id="props.id"
        :label="props.field.label"
        :description="props.field.description"
        :error="props.error"
        :required="props.field.required"
    >
        <UiInput
            v-if="['text', 'email', 'password'].includes(props.field.type)"
            :id="props.id"
            :type="props.field.type"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :placeholder="props.field.placeholder"
            :autocomplete="props.field.autocomplete"
            :disabled="props.field.disabled"
            :readonly="props.field.readonly"
            @update:model-value="emit('update:modelValue', $event)"
        />

        <UiTextarea
            v-else-if="props.field.type === 'textarea'"
            :id="props.id"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :placeholder="props.field.placeholder"
            :disabled="props.field.disabled"
            :readonly="props.field.readonly"
            @update:model-value="emit('update:modelValue', $event)"
        />

        <UiSelect
            v-else-if="props.field.type === 'select'"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :disabled="props.field.disabled"
            @update:model-value="emit('update:modelValue', $event)"
        >
            <UiSelectTrigger class="w-full">
                <UiSelectValue :placeholder="props.field.placeholder ?? 'Select an option'" />
            </UiSelectTrigger>
            <UiSelectContent>
                <UiSelectItem v-for="option in resolvedOptions" :key="option.value" :value="option.value" :disabled="option.disabled">
                    {{ option.label }}
                </UiSelectItem>
            </UiSelectContent>
        </UiSelect>

        <select
            v-else-if="props.field.type === 'multiselect'"
            :id="props.id"
            multiple
            :disabled="props.field.disabled"
            class="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
            @change="onMultiSelectChange"
        >
            <option
                v-for="option in resolvedOptions"
                :key="option.value"
                :value="option.value"
                :selected="Array.isArray(props.modelValue) && props.modelValue.includes(option.value)"
            >
                {{ option.label }}
            </option>
        </select>

        <UiInput
            v-else-if="props.field.type === 'file'"
            :id="props.id"
            type="file"
            :accept="props.field.accept"
            :multiple="props.field.multiple"
            :disabled="props.field.disabled"
            @change="onFileChange"
        />

        <div v-else-if="props.field.type === 'checkbox'" class="flex items-center gap-2">
            <UiCheckbox
                :id="props.id"
                :model-value="toBoolean(props.modelValue)"
                :disabled="props.field.disabled"
                @update:model-value="emit('update:modelValue', $event === true)"
            />
            <UiLabel :for="props.id">{{ props.field.placeholder ?? props.field.label }}</UiLabel>
        </div>

        <div v-else-if="props.field.type === 'toggle'" class="flex items-center justify-between gap-4 rounded-lg border p-3">
            <div class="space-y-0.5">
                <p class="text-sm font-medium">{{ props.field.placeholder ?? props.field.label }}</p>
                <p v-if="props.field.description !== undefined" class="text-xs text-muted-foreground">{{ props.field.description }}</p>
            </div>
            <UiSwitch
                :model-value="toBoolean(props.modelValue)"
                :disabled="props.field.disabled"
                @update:model-value="emit('update:modelValue', $event === true)"
            />
        </div>

        <UiRadioGroup
            v-else-if="props.field.type === 'radio'"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :disabled="props.field.disabled"
            class="space-y-2"
            @update:model-value="emit('update:modelValue', $event)"
        >
            <div v-for="option in resolvedOptions" :key="option.value" class="flex items-center gap-2">
                <UiRadioGroupItem :id="`${props.id}-${option.value}`" :value="option.value" :disabled="option.disabled" />
                <UiLabel :for="`${props.id}-${option.value}`">{{ option.label }}</UiLabel>
            </div>
        </UiRadioGroup>

        <UiTabs
            v-else-if="props.field.type === 'tabs'"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            @update:model-value="emit('update:modelValue', $event)"
        >
            <UiTabsList class="w-full">
                <UiTabsTrigger v-for="option in resolvedOptions" :key="option.value" :value="option.value" class="flex-1">
                    {{ option.label }}
                </UiTabsTrigger>
            </UiTabsList>
        </UiTabs>

        <UiInput
            v-else
            :id="props.id"
            :model-value="typeof props.modelValue === 'string' ? props.modelValue : ''"
            :placeholder="props.field.placeholder"
            :disabled="props.field.disabled"
            @update:model-value="emit('update:modelValue', $event)"
        />
    </BaseFormsBaseFieldShell>
</template>
