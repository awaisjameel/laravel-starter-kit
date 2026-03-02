<script setup lang="ts">
    interface Props {
        currentPage: number
        totalPages: number
        totalItems: number
        itemsPerPage: number
    }

    const props = defineProps<Props>()

    const emit = defineEmits<{
        pageChange: [page: number]
    }>()

    const pageNumbers = computed(() => {
        const pages: number[] = []
        const siblingCount = 2
        const safeTotalPages = Math.max(1, props.totalPages)
        const safeCurrentPage = Math.min(Math.max(1, props.currentPage), safeTotalPages)
        const start = Math.max(1, safeCurrentPage - siblingCount)
        const end = Math.min(safeTotalPages, safeCurrentPage + siblingCount)

        for (let page = start; page <= end; page += 1) {
            pages.push(page)
        }

        return pages
    })
</script>

<template>
    <div v-if="props.totalPages > 1" class="mt-2 w-full overflow-x-auto pb-1">
        <UiPagination
            class="min-w-max"
            :total="props.totalItems"
            :items-per-page="props.itemsPerPage"
            :page="props.currentPage"
            :sibling-count="2"
            show-edges
            @update:page="emit('pageChange', $event)"
        >
            <UiPaginationContent>
                <UiPaginationPrevious @click="emit('pageChange', props.currentPage - 1)" :disabled="props.currentPage === 1">
                    <Icon-mdi-chevron-left class="size-4" />
                </UiPaginationPrevious>

                <template v-for="page in pageNumbers" :key="page">
                    <UiPaginationItem :value="page" as-child>
                        <UiButton
                            class="h-9 w-9 p-0 sm:h-10 sm:w-10"
                            :variant="page === props.currentPage ? 'outline' : 'ghost'"
                            :aria-current="page === props.currentPage ? 'page' : undefined"
                            :disabled="page === props.currentPage"
                            @click="emit('pageChange', page)"
                        >
                            {{ page }}
                        </UiButton>
                    </UiPaginationItem>
                </template>

                <UiPaginationNext @click="emit('pageChange', props.currentPage + 1)" :disabled="props.currentPage === props.totalPages">
                    <Icon-mdi-chevron-right class="size-4" />
                </UiPaginationNext>
            </UiPaginationContent>
        </UiPagination>
    </div>
</template>
