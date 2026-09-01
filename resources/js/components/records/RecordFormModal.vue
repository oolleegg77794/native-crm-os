<template>
    <Modal
        :model-value="modelValue"
        :title="isEdit ? `Edit ${module.singular_name || module.name}` : `New ${module.singular_name || module.name}`"
        size="lg"
        @update:model-value="$emit('update:modelValue', $event)"
        @close="$emit('close')"
    >
        <form @submit.prevent="handleSubmit" id="record-form">
            <LayoutRenderer
                :module="module"
                :form-data="form"
                :errors="errors"
                :field-permissions="fieldPermissions"
                @update:form-data="form = $event"
            />
        </form>

        <template #footer>
            <button
                type="button"
                class="px-4 py-2 text-sm font-medium rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 transition"
                @click="$emit('update:modelValue', false)"
            >
                Cancel
            </button>
            <button
                type="submit"
                form="record-form"
                :disabled="saving"
                class="px-5 py-2 text-sm font-medium rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition disabled:opacity-50 flex items-center gap-2"
            >
                <svg v-if="saving" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ isEdit ? 'Save Changes' : 'Create Record' }}
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import Modal from '@/components/common/Modal.vue';
import LayoutRenderer from '@/components/records/LayoutRenderer.vue';
import { useRecordStore } from '@/stores/records';

const props = defineProps({
    modelValue: { type: Boolean, required: true },
    module: { type: Object, required: true },
    initialData: { type: Object, default: () => ({}) },
    fieldPermissions: { type: Object, default: () => ({}) },
    isEdit: { type: Boolean, default: false },
    recordId: { type: Number, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const recordStore = useRecordStore();
const form = ref({});
const errors = ref({});
const saving = ref(false);

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        errors.value = {};
        if (props.isEdit && props.initialData) {
            form.value = { ...props.initialData };
        } else {
            // Set default values from field definitions
            const initial = {};
            props.module.fields?.forEach((f) => {
                initial[f.api_name] = f.default_value ?? null;
            });
            form.value = initial;
        }
    }
});

const handleSubmit = async () => {
    errors.value = {};
    saving.value = true;

    try {
        let result;
        if (props.isEdit && props.recordId) {
            result = await recordStore.updateRecord(props.module.api_name, props.recordId, form.value);
        } else {
            result = await recordStore.createRecord(props.module.api_name, form.value);
        }

        emit('saved', result);
        emit('update:modelValue', false);
    } catch (err) {
        if (err.response?.data?.errors) {
            errors.value = err.response.data.errors;
        } else {
            alert(err.response?.data?.message || err.message);
        }
    } finally {
        saving.value = false;
    }
};
</script>
