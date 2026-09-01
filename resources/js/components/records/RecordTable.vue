<template>
    <div class="w-full overflow-hidden border border-slate-800 rounded-2xl bg-slate-900/60 glass-panel shadow-xl">
        <!-- Table Scroll Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-800/80 bg-slate-950/50 text-slate-400 text-xs uppercase font-semibold tracking-wider select-none">
                        <!-- Select All Checkbox -->
                        <th class="w-10 px-4 py-3.5 text-center">
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-950 cursor-pointer"
                                @change="$emit('toggleSelectAll')"
                            />
                        </th>

                        <!-- Dynamic Columns -->
                        <th
                            v-for="col in displayColumns"
                            :key="col.api_name"
                            class="px-4 py-3.5 cursor-pointer hover:text-slate-200 transition group"
                            @click="$emit('sort', col.api_name)"
                        >
                            <div class="flex items-center gap-1.5">
                                <span>{{ col.label }}</span>
                                <span v-if="sortBy === col.api_name" class="text-indigo-400">
                                    <svg v-if="sortDir === 'asc'" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                                <span v-else class="opacity-0 group-hover:opacity-40 text-slate-400 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </span>
                            </div>
                        </th>

                        <!-- Owner Column -->
                        <th class="px-4 py-3.5">Owner</th>

                        <!-- Updated Column -->
                        <th class="px-4 py-3.5">Updated</th>

                        <!-- Row Actions Column -->
                        <th class="w-20 px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-800/60">
                    <tr v-if="loading" class="animate-pulse">
                        <td :colspan="displayColumns.length + 4" class="px-4 py-12 text-center text-slate-400">
                            <div class="flex items-center justify-center gap-3">
                                <svg class="animate-spin h-5 w-5 text-indigo-400" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>Loading records...</span>
                            </div>
                        </td>
                    </tr>

                    <tr v-else-if="records.length === 0">
                        <td :colspan="displayColumns.length + 4" class="px-4 py-16 text-center">
                            <div class="max-w-sm mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto border border-indigo-500/20">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-200">No records found</h3>
                                <p class="text-xs text-slate-400">
                                    Get started by creating the first record or adjusting your search filters.
                                </p>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-lg shadow-indigo-600/20 transition"
                                    @click="$emit('create')"
                                >
                                    + Add New
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Records Rows -->
                    <tr
                        v-for="record in records"
                        :key="record.id"
                        class="hover:bg-slate-800/40 transition group cursor-pointer"
                        :class="selectedIds.includes(record.id) ? 'bg-indigo-950/20' : ''"
                        @click="$emit('view', record.id)"
                    >
                        <!-- Row Select Checkbox -->
                        <td class="px-4 py-3.5 text-center" @click.stop>
                            <input
                                type="checkbox"
                                :checked="selectedIds.includes(record.id)"
                                class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-950 cursor-pointer"
                                @change="$emit('toggleSelect', record.id)"
                            />
                        </td>

                        <!-- Cell Values -->
                        <td
                            v-for="col in displayColumns"
                            :key="col.api_name"
                            class="px-4 py-3.5 whitespace-nowrap text-slate-200"
                        >
                            <!-- Lookup Value -->
                            <div v-if="col.type === 'lookup'" class="flex items-center gap-1.5">
                                <span v-if="record.lookups?.[col.api_name]" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-indigo-500/10 text-indigo-300 text-xs font-medium border border-indigo-500/20">
                                    {{ record.lookups[col.api_name].label }}
                                </span>
                                <span v-else class="text-slate-500 text-xs">—</span>
                            </div>

                            <!-- Currency Value -->
                            <div v-else-if="col.type === 'currency'" class="font-mono text-emerald-400 font-medium">
                                {{ formatCurrency(record.raw_values?.[col.api_name], col.options?.currency_code) }}
                            </div>

                            <!-- Picklist Value -->
                            <div v-else-if="col.type === 'picklist'">
                                <Badge v-if="record.raw_values?.[col.api_name]" variant="primary">
                                    {{ getPicklistLabel(col, record.raw_values[col.api_name]) }}
                                </Badge>
                                <span v-else class="text-slate-500 text-xs">—</span>
                            </div>

                            <!-- Boolean Value -->
                            <div v-else-if="col.type === 'boolean'">
                                <span
                                    class="inline-block w-2.5 h-2.5 rounded-full"
                                    :class="record.raw_values?.[col.api_name] ? 'bg-emerald-400' : 'bg-slate-600'"
                                />
                            </div>

                            <!-- Date Value -->
                            <div v-else-if="col.type === 'date' || col.type === 'datetime'" class="text-slate-300 font-mono text-xs">
                                {{ record.raw_values?.[col.api_name] || '—' }}
                            </div>

                            <!-- Default Text Value (Bold for Title Field) -->
                            <div v-else :class="col.api_name === module.title_field ? 'font-semibold text-white group-hover:text-indigo-400 transition' : 'text-slate-300'">
                                {{ record.raw_values?.[col.api_name] || '—' }}
                            </div>
                        </td>

                        <!-- Owner -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-400">
                            {{ record.owner?.name || '—' }}
                        </td>

                        <!-- Updated At -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500 font-mono">
                            {{ formatDate(record.updated_at) }}
                        </td>

                        <!-- Row Actions -->
                        <td class="px-4 py-3.5 text-right whitespace-nowrap" @click.stop>
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition">
                                <button
                                    type="button"
                                    class="p-1 text-slate-400 hover:text-indigo-400 rounded-lg hover:bg-slate-800 transition"
                                    title="Edit"
                                    @click="$emit('edit', record)"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="p-1 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-800 transition"
                                    title="Delete"
                                    @click="$emit('delete', record.id)"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import Badge from '@/components/common/Badge.vue';

const props = defineProps({
    module: { type: Object, required: true },
    records: { type: Array, required: true },
    selectedColumns: { type: Array, default: null },
    selectedIds: { type: Array, default: () => [] },
    allSelected: { type: Boolean, default: false },
    sortBy: { type: String, default: 'id' },
    sortDir: { type: String, default: 'desc' },
    loading: { type: Boolean, default: false },
});

defineEmits(['toggleSelectAll', 'toggleSelect', 'sort', 'view', 'edit', 'delete', 'create']);

const displayColumns = computed(() => {
    const allFields = props.module.fields || [];

    if (props.selectedColumns && props.selectedColumns.length > 0) {
        return props.selectedColumns
            .map((apiName) => allFields.find((f) => f.api_name === apiName))
            .filter(Boolean);
    }

    // Default to first 5-6 fields
    return allFields.slice(0, 6);
});

const formatCurrency = (val, currencyCode = 'USD') => {
    if (val === null || val === undefined || val === '') return '—';
    const num = Number(val);
    const sym = currencyCode === 'EUR' ? '€' : currencyCode === 'UAH' ? '₴' : '$';
    return `${sym}${num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const getPicklistLabel = (col, key) => {
    const values = col.options?.values || [];
    const item = values.find((v) => v.key === key);
    return item ? item.label : key;
};

const formatDate = (isoStr) => {
    if (!isoStr) return '—';
    const d = new Date(isoStr);
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>
