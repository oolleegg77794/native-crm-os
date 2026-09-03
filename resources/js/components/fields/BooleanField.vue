<template>
    <div class="flex items-center justify-between py-2">
        <div>
            <label class="text-sm font-medium text-slate-800 dark:text-slate-200 cursor-pointer select-none" @click="toggle">
                {{ label }}
                <span v-if="required" class="text-rose-500 font-bold">*</span>
            </label>
            <p v-if="description" class="text-xs text-slate-500 dark:text-slate-400">
                {{ description }}
            </p>
        </div>

        <button
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :disabled="readonly"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="modelValue ? 'bg-indigo-600' : 'bg-slate-200 dark:bg-slate-800'"
            @click="toggle"
        >
            <span
                aria-hidden="true"
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                :class="modelValue ? 'translate-x-5' : 'translate-x-0'"
            />
        </button>
    </div>
</template>

<script setup>
const props = defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, default: '' },
    description: { type: String, default: '' },
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const toggle = () => {
    if (!props.readonly) {
        emit('update:modelValue', !props.modelValue);
    }
};
</script>
