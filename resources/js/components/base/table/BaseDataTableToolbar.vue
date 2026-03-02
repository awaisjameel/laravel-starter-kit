<script setup lang="ts">
    interface Props {
        searchValue: string
        searchPlaceholder?: string
        perPage?: number
        perPageOptions?: number[]
    }

    const props = withDefaults(defineProps<Props>(), {
        searchPlaceholder: 'Search...',
        perPage: undefined,
        perPageOptions: () => [10, 25, 50, 100]
    })

    const emit = defineEmits<{
        'update:searchValue': [value: string]
        'update:perPage': [value: number]
    }>()
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
            <UiInput
                :model-value="props.searchValue"
                class="w-full sm:w-72"
                type="search"
                :placeholder="props.searchPlaceholder"
                @update:model-value="emit('update:searchValue', String($event))"
            />
            <UiSelect
                v-if="props.perPage !== undefined"
                :model-value="String(props.perPage)"
                @update:model-value="emit('update:perPage', Number($event))"
            >
                <UiSelectTrigger class="w-full sm:w-40">
                    <UiSelectValue />
                </UiSelectTrigger>
                <UiSelectContent>
                    <UiSelectItem v-for="option in props.perPageOptions" :key="option" :value="String(option)"> {{ option }} per page </UiSelectItem>
                </UiSelectContent>
            </UiSelect>
        </div>

        <slot name="actions" />
    </div>
</template>
