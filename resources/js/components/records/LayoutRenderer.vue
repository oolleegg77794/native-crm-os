<template>
    <div class="space-y-6">
        <!-- Sections Layout -->
        <div
            v-for="(section, sIdx) in formSections"
            :key="sIdx"
            class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 space-y-4"
        >
            <h4 class="text-sm font-semibold text-indigo-400 tracking-wide uppercase flex items-center gap-2">
                <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                {{ section.title }}
            </h4>

            <div class="grid gap-4" :class="gridColsClass(section.columns || 2)">
                <div
                    v-for="fieldApi in section.fields"
                    :key="fieldApi"
                    :class="section.fullWidthFields?.includes(fieldApi) ? 'col-span-full' : ''"
                >
                    <FieldRenderer
                        v-if="getField(fieldApi)"
                        :field="getField(fieldApi)"
                        :model-value="formData[fieldApi]"
                        :lookup-label="lookupLabels[fieldApi] || ''"
                        :readonly="isFieldDisabled(fieldApi)"
                        :error="errors[fieldApi]"
                        @update:model-value="updateField(fieldApi, $event)"
                        @update:lookup-label="lookupLabels[fieldApi] = $event"
                    />
                </div>
            </div>
        </div>

        <!-- Fallback if any fields not placed in sections -->
        <div
            v-if="unassignedFields.length > 0"
            class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 space-y-4"
        >
            <h4 class="text-sm font-semibold text-slate-400 tracking-wide uppercase flex items-center gap-2">
                <span class="w-1.5 h-4 bg-slate-600 rounded-full"></span>
                Additional Details
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="field in unassignedFields" :key="field.api_name">
                    <FieldRenderer
                        :field="field"
                        :model-value="formData[field.api_name]"
                        :lookup-label="lookupLabels[field.api_name] || ''"
                        :readonly="isFieldDisabled(field.api_name)"
                        :error="errors[field.api_name]"
                        @update:model-value="updateField(field.api_name, $event)"
                        @update:lookup-label="lookupLabels[field.api_name] = $event"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive } from 'vue';
import FieldRenderer from '@/components/fields/FieldRenderer.vue';

const props = defineProps({
    module: { type: Object, required: true },
    formData: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) },
    fieldPermissions: { type: Object, default: () => ({}) }, // { [fieldApi]: { access: 'read' | 'write' } }
});

const emit = defineEmits(['update:formData']);

const lookupLabels = reactive({});

const allFields = computed(() => props.module?.fields || []);

const formSections = computed(() => {
    if (props.module?.layout?.sections && props.module.layout.sections.length > 0) {
        return props.module.layout.sections;
    }

    // Default layout: 1 section with all fields
    return [
        {
            title: 'General Information',
            columns: 2,
            fields: allFields.value.map((f) => f.api_name),
        },
    ];
});

const assignedFieldApis = computed(() => {
    const apis = [];
    formSections.value.forEach((sec) => {
        if (Array.isArray(sec.fields)) apis.push(...sec.fields);
    });
    return apis;
});

const unassignedFields = computed(() =>
    allFields.value.filter((f) => !assignedFieldApis.value.includes(f.api_name))
);

const getField = (apiName) => allFields.value.find((f) => f.api_name === apiName);

const isFieldDisabled = (apiName) => {
    const perm = props.fieldPermissions[apiName];
    if (perm && perm.access === 'read') return true;
    return false;
};

const updateField = (apiName, value) => {
    const updated = { ...props.formData, [apiName]: value };
    emit('update:formData', updated);
};

const gridColsClass = (cols) => {
    switch (cols) {
        case 1: return 'grid-cols-1';
        case 3: return 'grid-cols-1 md:grid-cols-3';
        case 2:
        default: return 'grid-cols-1 md:grid-cols-2';
    }
};
</script>
