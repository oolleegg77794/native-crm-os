<template>
    <Drawer
        :model-value="modelValue"
        title="Filter Records"
        subtitle="Refine table results by applying dynamic field conditions"
        @update:model-value="$emit('update:modelValue', $event)"
        @close="$emit('close')"
    >
        <div class="space-y-5">
            <!-- Filter Logic (AND / OR) -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800">
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Match conditions:</span>
                <div class="flex items-center gap-1 bg-white dark:bg-slate-900 p-0.5 rounded-lg border border-slate-200 dark:border-slate-800">
                    <button
                        type="button"
                        class="px-2.5 py-1 rounded-md text-xs font-medium transition"
                        :class="logic === 'and' ? 'bg-indigo-600 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        @click="logic = 'and'"
                    >
                        AND (All)
                    </button>
                    <button
                        type="button"
                        class="px-2.5 py-1 rounded-md text-xs font-medium transition"
                        :class="logic === 'or' ? 'bg-indigo-600 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        @click="logic = 'or'"
                    >
                        OR (Any)
                    </button>
                </div>
            </div>

            <!-- Conditions List -->
            <div class="space-y-3">
                <div
                    v-for="(rule, rIdx) in conditions"
                    :key="rIdx"
                    class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 space-y-2.5"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Rule #{{ rIdx + 1 }}</span>
                        <button
                            type="button"
                            class="text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 p-0.5 rounded transition"
                            @click="removeRule(rIdx)"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- Field Select -->
                    <select
                        v-model="rule.field"
                        class="w-full px-3 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-800 dark:text-slate-100 focus:outline-none focus:border-indigo-500"
                    >
                        <option value="" disabled selected>Select field...</option>
                        <option v-for="f in module.fields" :key="f.api_name" :value="f.api_name">
                            {{ f.label }}
                        </option>
                    </select>

                    <!-- Operator Select -->
                    <select
                        v-model="rule.operator"
                        class="w-full px-3 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-800 dark:text-slate-100 focus:outline-none focus:border-indigo-500"
                    >
                        <option value="=">Equals (=)</option>
                        <option value="!=">Not Equals (!=)</option>
                        <option value="contains">Contains</option>
                        <option value="starts_with">Starts With</option>
                        <option value=">">Greater Than (&gt;)</option>
                        <option value=">=">Greater or Equal (&gt;=)</option>
                        <option value="<">Less Than (&lt;)</option>
                        <option value="<=">Less or Equal (&lt;=)</option>
                        <option value="is_null">Is Empty / Null</option>
                        <option value="is_not_null">Is Not Empty</option>
                    </select>

                    <!-- Value Input (if not null check) -->
                    <input
                        v-if="!['is_null', 'is_not_null'].includes(rule.operator)"
                        type="text"
                        v-model="rule.value"
                        placeholder="Value..."
                        class="w-full px-3 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500"
                    />
                </div>
            </div>

            <!-- Add Rule Button -->
            <button
                type="button"
                class="w-full py-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 border border-dashed border-indigo-500/30 hover:border-indigo-500/60 rounded-xl transition flex items-center justify-center gap-1.5"
                @click="addRule"
            >
                + Add Condition
            </button>
        </div>

        <template #footer>
            <button
                type="button"
                class="px-3 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                @click="clearFilters"
            >
                Reset
            </button>
            <button
                type="button"
                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition"
                @click="applyFilters"
            >
                Apply Filters
            </button>
        </template>
    </Drawer>
</template>

<script setup>
import { ref } from 'vue';
import Drawer from '@/components/common/Drawer.vue';

const props = defineProps({
    modelValue: { type: Boolean, required: true },
    module: { type: Object, required: true },
});

const emit = defineEmits(['update:modelValue', 'apply', 'close']);

const logic = ref('and');
const conditions = ref([
    { field: '', operator: '=', value: '' },
]);

const addRule = () => {
    conditions.value.push({ field: '', operator: '=', value: '' });
};

const removeRule = (idx) => {
    conditions.value.splice(idx, 1);
};

const clearFilters = () => {
    conditions.value = [];
    emit('apply', { conditions: [], logic: 'and' });
    emit('update:modelValue', false);
};

const applyFilters = () => {
    const valid = conditions.value.filter((r) => r.field);
    emit('apply', { conditions: valid, logic: logic.value });
    emit('update:modelValue', false);
};
</script>
