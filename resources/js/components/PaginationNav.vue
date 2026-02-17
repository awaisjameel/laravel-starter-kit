<script setup lang="ts">
    import type { Paginated } from '@/types'
    import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

    /**
     * Reusable pagination component for server-side paginated data.
     * Follows the project's pagination patterns from users/Index.vue.
     */
    const props = defineProps<{
        pagination: Paginated<unknown>
    }>()

    const emit = defineEmits<{
        'page-change': [page: number]
    }>()

    const currentPage = computed(() => Number(props.pagination.current_page) || 1)
    const totalPages = computed(() => Number(props.pagination.last_page) || 1)
    const totalItems = computed(() => Number(props.pagination.total) || 0)
    const itemsPerPage = computed(() => Number(props.pagination.per_page) || 10)

    const pageNumbers = computed(() => {
        const pages: number[] = []
        const siblingCount = 2
        const validTotalPages = Math.max(1, totalPages.value)
        const validCurrentPage = Math.max(1, Math.min(currentPage.value, validTotalPages))
        const start = Math.max(1, validCurrentPage - siblingCount)
        const end = Math.min(validTotalPages, validCurrentPage + siblingCount)

        for (let i = start; i <= end; i++) {
            pages.push(i)
        }

        return pages.length > 0 ? pages : [1]
    })

    const onPageChange = (page: number) => {
        if (page !== currentPage.value && page >= 1 && page <= totalPages.value) {
            emit('page-change', page)
        }
    }
</script>

<template>
    <div v-if="totalPages > 1" class="mt-4 flex flex-row items-center justify-between">
        <UiPagination
            :total="totalItems"
            :items-per-page="itemsPerPage"
            :page="currentPage"
            :sibling-count="2"
            show-edges
            @update:page="onPageChange"
        >
            <UiPaginationContent>
                <UiPaginationPrevious @click="onPageChange(currentPage - 1)" :disabled="currentPage === 1">
                    <ChevronLeft class="h-4 w-4" />
                </UiPaginationPrevious>
                <template v-for="(item, index) in pageNumbers" :key="index">
                    <UiPaginationItem :value="item" as-child>
                        <UiButton
                            class="h-10 w-10 p-0"
                            :variant="item === currentPage ? 'outline' : 'ghost'"
                            :aria-current="item === currentPage ? 'page' : undefined"
                            :disabled="item === currentPage"
                            @click="onPageChange(item)"
                        >
                            {{ item }}
                        </UiButton>
                    </UiPaginationItem>
                </template>
                <UiPaginationNext @click="onPageChange(currentPage + 1)" :disabled="currentPage === totalPages">
                    <ChevronRight class="h-4 w-4" />
                </UiPaginationNext>
            </UiPaginationContent>
        </UiPagination>
    </div>
</template>
