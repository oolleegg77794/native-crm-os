<template>
    <div>
        <label v-if="label" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
            {{ label }}
            <span v-if="required" class="text-rose-400 font-bold">*</span>
        </label>

        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-xs font-semibold">
                {{ currencySymbol }}
            </div>
            <input
                type="number"
                step="0.01"
                :value="modelValue"
                :disabled="readonly"
                :placeholder="placeholder"
                class="w-full pl-8 pr-3.5 py-2 text-sm bg-slate-950/60 border rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition disabled:opacity-50 disabled:bg-slate-900 disabled:cursor-not-allowed font-mono"
                :class="error ? 'border-rose-500/80 ring-1 ring-rose-500/30' : 'border-slate-800'"
                @input="$emit('update:modelValue', $event.target.value === '' ? null : Number($event.target.value))"
            />
        </div>

        <p v-if="error" class="mt-1 text-xs text-rose-400 font-medium">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: [Number, String], default: null },
    label: { type: String, default: '' },
    currencyCode: { type: String, default: 'USD' },
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    placeholder: { type: String, default: '0.00' },
    error: { type: String, default: '' },
});

defineEmits(['update:modelValue']);

const currencySymbol = computed(() => {
    switch (props.currencyCode) {
        case 'EUR': return '€';
        case 'UAH': return '₴';
        case 'GBP': return '£';
        case 'USD':
        default: return '$';
    }
});
</script>
