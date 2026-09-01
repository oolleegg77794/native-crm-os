<template>
    <div>
        <label v-if="label" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
            {{ label }}
            <span v-if="required" class="text-rose-400 font-bold">*</span>
        </label>

        <div class="relative">
            <textarea
                v-if="type === 'text' || type === 'longtext'"
                :value="modelValue"
                :disabled="readonly"
                :rows="type === 'longtext' ? 4 : 2"
                :placeholder="placeholder"
                class="w-full px-3.5 py-2 text-sm bg-slate-950/60 border rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition disabled:opacity-50 disabled:bg-slate-900 disabled:cursor-not-allowed"
                :class="error ? 'border-rose-500/80 ring-1 ring-rose-500/30' : 'border-slate-800'"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <input
                v-else
                :type="inputType"
                :value="modelValue"
                :disabled="readonly"
                :placeholder="placeholder"
                class="w-full px-3.5 py-2 text-sm bg-slate-950/60 border rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition disabled:opacity-50 disabled:bg-slate-900 disabled:cursor-not-allowed"
                :class="error ? 'border-rose-500/80 ring-1 ring-rose-500/30' : 'border-slate-800'"
                @input="$emit('update:modelValue', $event.target.value)"
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
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    type: { type: String, default: 'string' },
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
});

defineEmits(['update:modelValue']);

const inputType = computed(() => {
    switch (props.type) {
        case 'email': return 'email';
        case 'url': return 'url';
        case 'phone': return 'tel';
        default: return 'text';
    }
});
</script>
