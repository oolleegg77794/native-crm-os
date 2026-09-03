<template>
    <div>
        <label v-if="label" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
            {{ label }}
            <span v-if="required" class="text-rose-500 font-bold">*</span>
        </label>

        <div class="relative">
            <select
                :value="modelValue"
                :disabled="readonly"
                class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition disabled:opacity-50 disabled:bg-slate-100 dark:disabled:bg-slate-900 disabled:cursor-not-allowed appearance-none cursor-pointer"
                :class="error ? 'border-rose-500 ring-1 ring-rose-500/30' : 'border-slate-200 dark:border-slate-800'"
                @change="$emit('update:modelValue', $event.target.value)"
            >
                <option value="" disabled selected>{{ placeholder || 'Select an option...' }}</option>
                <option
                    v-for="opt in options"
                    :key="opt.key"
                    :value="opt.key"
                    class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
                >
                    {{ opt.label }}
                </option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 dark:text-slate-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <p v-if="error" class="mt-1 text-xs text-rose-400 font-medium">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    options: { type: Array, default: () => [] }, // [{ key, label, color }]
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
});

defineEmits(['update:modelValue']);
</script>
