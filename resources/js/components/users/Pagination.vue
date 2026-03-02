<script setup lang="ts">
    interface Props {
        currentPage: number
        itemsPerPage: number
        totalItems: number
        totalPages: number
    }

    defineProps<Props>()

    const emit = defineEmits<{
        pageChange: [page: number]
    }>()
</script>

<template>
    <BaseTableBaseDataTablePagination
        :current-page="currentPage"
        :items-per-page="itemsPerPage"
        :total-items="totalItems"
        :total-pages="totalPages"
        @page-change="emit('pageChange', $event)"
    />
</template>
