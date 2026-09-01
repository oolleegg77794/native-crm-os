<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="modelValue"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
                @click.self="close"
            >
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div
                        v-if="modelValue"
                        class="w-full max-h-[90vh] flex flex-col bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden glass-panel"
                        :class="sizeClass"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/80">
                            <div>
                                <h3 class="text-lg font-semibold text-white tracking-tight">
                                    {{ title }}
                                </h3>
                                <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">
                                    {{ subtitle }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition"
                                @click="close"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="p-6 overflow-y-auto flex-1">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-800/80 bg-slate-900/50">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, required: true },
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    size: { type: String, default: 'md' }, // sm, md, lg, xl, 2xl, full
});

const emit = defineEmits(['update:modelValue', 'close']);

const close = () => {
    emit('update:modelValue', false);
    emit('close');
};

const sizeClass = computed(() => {
    switch (props.size) {
        case 'sm': return 'max-w-md';
        case 'lg': return 'max-w-3xl';
        case 'xl': return 'max-w-5xl';
        case '2xl': return 'max-w-6xl';
        case 'full': return 'max-w-[95vw] h-[90vh]';
        case 'md':
        default: return 'max-w-xl';
    }
});
</script>
