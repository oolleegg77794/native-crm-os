<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-6"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-6"
    >
        <div
            v-if="count > 0"
            class="fixed bottom-8 left-1/2 -translate-x-1/2 z-40 flex items-center gap-3 px-5 py-3 rounded-2xl bg-white/95 dark:bg-slate-900/95 border border-indigo-500/30 text-slate-900 dark:text-white shadow-2xl glass-dropdown glow-indigo"
        >
            <div class="flex items-center gap-2 pr-3 border-r border-slate-200 dark:border-slate-700">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-xs font-bold text-white">
                    {{ count }}
                </span>
                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">selected</span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition flex items-center gap-1.5 shadow-sm"
                    @click="$emit('massUpdate')"
                >
                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Update
                </button>

                <button
                    type="button"
                    class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-300 border border-rose-500/20 transition flex items-center gap-1.5 shadow-sm"
                    @click="$emit('massDelete')"
                >
                    <svg class="w-3.5 h-3.5 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>

                <button
                    type="button"
                    class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition ml-2"
                    title="Clear selection"
                    @click="$emit('clear')"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>

<script setup>
defineProps({
    count: { type: Number, required: true },
});

defineEmits(['massUpdate', 'massDelete', 'clear']);
</script>
