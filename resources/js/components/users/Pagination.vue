<script setup lang="ts">
    interface Props {
        currentPage: number
        itemsPerPage: number
        pageNumbers: number[]
        totalItems: number
        totalPages: number
    }

    defineProps<Props>()

    const emit = defineEmits<{
        pageChange: [page: number]
    }>()
</script>

<template>
    <div v-if="totalPages > 1" class="mt-2 w-full overflow-x-auto pb-1">
        <UiPagination
            class="min-w-max"
            :total="totalItems"
            :items-per-page="itemsPerPage"
            :page="currentPage"
            :sibling-count="2"
            show-edges
            @update:page="emit('pageChange', $event)"
        >
            <UiPaginationContent>
                <UiPaginationPrevious @click="emit('pageChange', currentPage - 1)" :disabled="currentPage === 1">
                    <Icon-mdi-chevron-left class="h-4 w-4" />
                </UiPaginationPrevious>
                <template v-for="(item, index) in pageNumbers" :key="index">
                    <UiPaginationItem :value="item" as-child>
                        <UiButton
                            class="h-9 w-9 p-0 sm:h-10 sm:w-10"
                            :variant="item === currentPage ? 'outline' : 'ghost'"
                            :aria-current="item === currentPage ? 'page' : undefined"
                            :disabled="item === currentPage"
                            @click="emit('pageChange', item)"
                        >
                            {{ item }}
                        </UiButton>
                    </UiPaginationItem>
                </template>
                <UiPaginationNext @click="emit('pageChange', currentPage + 1)" :disabled="currentPage === totalPages">
                    <Icon-mdi-chevron-right class="h-4 w-4" />
                </UiPaginationNext>
            </UiPaginationContent>
        </UiPagination>
    </div>
</template>
