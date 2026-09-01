<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="modelValue"
                class="fixed inset-0 z-50 flex justify-end bg-slate-950/70 backdrop-blur-sm"
                @click.self="close"
            >
                <Transition
                    enter-active-class="transition duration-300 ease-out transform"
                    enter-from-class="translate-x-full"
                    enter-to-class="translate-x-0"
                    leave-active-class="transition duration-200 ease-in transform"
                    leave-from-class="translate-x-0"
                    leave-to-class="translate-x-full"
                >
                    <div
                        v-if="modelValue"
                        class="w-full max-w-md h-full flex flex-col bg-slate-900 border-l border-slate-800 shadow-2xl overflow-hidden glass-panel"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-base font-semibold text-white">
                                    {{ title }}
                                </h3>
                                <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">
                                    {{ subtitle }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition"
                                @click="close"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="p-6 overflow-y-auto flex-1">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-800 bg-slate-900/60">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
defineProps({
    modelValue: { type: Boolean, required: true },
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'close']);

const close = () => {
    emit('update:modelValue', false);
    emit('close');
};
</script>
