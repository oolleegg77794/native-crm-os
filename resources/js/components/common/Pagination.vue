<template>
    <div class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-900/40">
        <!-- Info -->
        <div class="text-xs text-slate-600 dark:text-slate-400">
            Showing <span class="font-medium text-slate-900 dark:text-slate-200">{{ from }}</span> to
            <span class="font-medium text-slate-900 dark:text-slate-200">{{ to }}</span> of
            <span class="font-medium text-slate-900 dark:text-slate-200">{{ total }}</span> records
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-2">
            <button
                type="button"
                :disabled="currentPage <= 1"
                class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-40 disabled:pointer-events-none transition shadow-sm"
                @click="$emit('changePage', currentPage - 1)"
            >
                Previous
            </button>

            <span class="text-xs text-slate-500 dark:text-slate-400 px-2">
                Page <span class="font-semibold text-slate-900 dark:text-slate-200">{{ currentPage }}</span> of
                <span class="font-semibold text-slate-900 dark:text-slate-200">{{ lastPage }}</span>
            </span>

            <button
                type="button"
                :disabled="currentPage >= lastPage"
                class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-40 disabled:pointer-events-none transition shadow-sm"
                @click="$emit('changePage', currentPage + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
    perPage: { type: Number, required: true },
    total: { type: Number, required: true },
});

defineEmits(['changePage']);

const from = computed(() => (props.total === 0 ? 0 : (props.currentPage - 1) * props.perPage + 1));
const to = computed(() => Math.min(props.currentPage * props.perPage, props.total));
</script>
