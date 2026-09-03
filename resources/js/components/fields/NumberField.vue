<template>
    <div>
        <label v-if="label" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
            {{ label }}
            <span v-if="required" class="text-rose-500 font-bold">*</span>
        </label>

        <div class="relative">
            <input
                type="number"
                :step="type === 'decimal' || type === 'percent' ? '0.01' : '1'"
                :value="modelValue"
                :disabled="readonly"
                :placeholder="placeholder"
                class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition disabled:opacity-50 disabled:bg-slate-100 dark:disabled:bg-slate-900 disabled:cursor-not-allowed"
                :class="[
                    error ? 'border-rose-500 ring-1 ring-rose-500/30' : 'border-slate-200 dark:border-slate-800',
                    type === 'percent' ? 'pr-8' : '',
                ]"
                @input="$emit('update:modelValue', $event.target.value === '' ? null : Number($event.target.value))"
            />
            <div v-if="type === 'percent'" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 dark:text-slate-500 text-xs font-semibold">
                %
            </div>
        </div>

        <p v-if="error" class="mt-1 text-xs text-rose-400 font-medium">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: [Number, String], default: null },
    label: { type: String, default: '' },
    type: { type: String, default: 'integer' }, // integer, decimal, percent
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
});

defineEmits(['update:modelValue']);
</script>
