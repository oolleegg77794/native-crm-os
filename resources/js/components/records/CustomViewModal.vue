<template>
    <Modal
        :model-value="modelValue"
        title="Customize Table Columns"
        subtitle="Choose which fields to display and arrange in the table view"
        size="md"
        @update:model-value="$emit('update:modelValue', $event)"
        @close="$emit('close')"
    >
        <div class="space-y-4">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span>Select fields ({{ selectedFields.length }} selected):</span>
                <button
                    type="button"
                    class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                    @click="selectAll"
                >
                    Select All
                </button>
            </div>

            <div class="max-h-72 overflow-y-auto space-y-1.5 p-1">
                <label
                    v-for="field in module.fields"
                    :key="field.api_name"
                    class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800/80 hover:border-slate-300 dark:hover:border-slate-700 cursor-pointer transition select-none"
                    :class="selectedFields.includes(field.api_name) ? 'border-indigo-500 dark:border-indigo-500/40 bg-indigo-50/60 dark:bg-indigo-950/10' : ''"
                >
                    <div class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            :value="field.api_name"
                            v-model="selectedFields"
                            class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-white dark:focus:ring-offset-slate-950 cursor-pointer"
                        />
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ field.label }}</span>
                    </div>

                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">{{ field.type }}</span>
                </label>
            </div>
        </div>

        <template #footer>
            <button
                type="button"
                class="px-4 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-sm"
                @click="$emit('update:modelValue', false)"
            >
                Cancel
            </button>
            <button
                type="button"
                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition"
                @click="saveColumns"
            >
                Apply Columns
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import Modal from '@/components/common/Modal.vue';

const props = defineProps({
    modelValue: { type: Boolean, required: true },
    module: { type: Object, required: true },
    currentColumns: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'save', 'close']);

const selectedFields = ref([]);

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        if (props.currentColumns && props.currentColumns.length > 0) {
            selectedFields.value = [...props.currentColumns];
        } else {
            selectedFields.value = (props.module.fields || []).slice(0, 6).map((f) => f.api_name);
        }
    }
});

const selectAll = () => {
    selectedFields.value = (props.module.fields || []).map((f) => f.api_name);
};

const saveColumns = () => {
    emit('save', selectedFields.value);
    emit('update:modelValue', false);
};
</script>
