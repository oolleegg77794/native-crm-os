<template>
    <div class="field-container">
        <!-- Picklist -->
        <PicklistField
            v-if="fieldType === 'picklist'"
            :model-value="modelValue"
            :label="field.label"
            :options="field.options?.values || []"
            :required="field.is_required"
            :readonly="isFieldReadonly"
            :error="error"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- Lookup -->
        <LookupField
            v-else-if="fieldType === 'lookup'"
            :model-value="modelValue"
            :initial-label="lookupLabel"
            :label="field.label"
            :target-module="field.options?.target_module || ''"
            :required="field.is_required"
            :readonly="isFieldReadonly"
            :error="error"
            @update:model-value="$emit('update:modelValue', $event)"
            @update:label="$emit('update:lookupLabel', $event)"
        />

        <!-- Currency -->
        <CurrencyField
            v-else-if="fieldType === 'currency'"
            :model-value="modelValue"
            :label="field.label"
            :currency-code="field.options?.currency_code || 'USD'"
            :required="field.is_required"
            :readonly="isFieldReadonly"
            :error="error"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- Numeric -->
        <NumberField
            v-else-if="['integer', 'decimal', 'percent'].includes(fieldType)"
            :model-value="modelValue"
            :label="field.label"
            :type="fieldType"
            :required="field.is_required"
            :readonly="isFieldReadonly"
            :error="error"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- Date / Datetime / Time -->
        <DateField
            v-else-if="['date', 'datetime', 'time'].includes(fieldType)"
            :model-value="modelValue"
            :label="field.label"
            :type="fieldType"
            :required="field.is_required"
            :readonly="isFieldReadonly"
            :error="error"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- Boolean -->
        <BooleanField
            v-else-if="fieldType === 'boolean'"
            :model-value="Boolean(modelValue)"
            :label="field.label"
            :required="field.is_required"
            :readonly="isFieldReadonly"
            @update:model-value="$emit('update:modelValue', $event)"
        />

        <!-- Default String / Text / Email / Phone / URL -->
        <StringField
            v-else
            :model-value="modelValue"
            :label="field.label"
            :type="fieldType"
            :required="field.is_required"
            :readonly="isFieldReadonly"
            :error="error"
            @update:model-value="$emit('update:modelValue', $event)"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import StringField from './StringField.vue';
import NumberField from './NumberField.vue';
import CurrencyField from './CurrencyField.vue';
import DateField from './DateField.vue';
import BooleanField from './BooleanField.vue';
import PicklistField from './PicklistField.vue';
import LookupField from './LookupField.vue';

const props = defineProps({
    field: { type: Object, required: true },
    modelValue: { type: [String, Number, Boolean, Array, Object], default: null },
    lookupLabel: { type: String, default: '' },
    readonly: { type: Boolean, default: false },
    error: { type: String, default: '' },
});

defineEmits(['update:modelValue', 'update:lookupLabel']);

const fieldType = computed(() => props.field.type || 'string');
const isFieldReadonly = computed(() => props.readonly || Boolean(props.field.is_readonly));
</script>
