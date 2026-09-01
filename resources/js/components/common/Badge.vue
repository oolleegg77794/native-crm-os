<template>
    <span
        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium tracking-wide transition"
        :class="variantClass"
    >
        <span v-if="dot" class="w-1.5 h-1.5 rounded-full" :class="dotClass"></span>
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: { type: String, default: 'default' }, // default, primary, success, warning, danger, info
    dot: { type: Boolean, default: false },
});

const variantClass = computed(() => {
    switch (props.variant) {
        case 'primary': return 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20';
        case 'success': return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
        case 'warning': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
        case 'danger': return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
        case 'info': return 'bg-sky-500/10 text-sky-400 border border-sky-500/20';
        case 'default':
        default: return 'bg-slate-800 text-slate-300 border border-slate-700/60';
    }
});

const dotClass = computed(() => {
    switch (props.variant) {
        case 'primary': return 'bg-indigo-400';
        case 'success': return 'bg-emerald-400';
        case 'warning': return 'bg-amber-400';
        case 'danger': return 'bg-rose-400';
        case 'info': return 'bg-sky-400';
        default: return 'bg-slate-400';
    }
});
</script>
