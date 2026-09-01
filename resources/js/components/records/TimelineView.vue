<template>
    <div class="space-y-4">
        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Activity Timeline ({{ timeline.length }})
        </h4>

        <div v-if="timeline.length === 0" class="text-xs text-slate-500 py-4 text-center">
            No activity history yet.
        </div>

        <div v-else class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
            <div
                v-for="event in timeline"
                :key="event.id"
                class="relative group"
            >
                <!-- Dot icon -->
                <div
                    class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2 border-slate-900 flex items-center justify-center"
                    :class="eventColor(event.event_type)"
                >
                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                </div>

                <!-- Event Details Card -->
                <div class="p-3.5 rounded-xl bg-slate-900/50 border border-slate-800/80 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5 font-medium text-slate-200">
                            <span class="capitalize font-semibold text-indigo-400">{{ event.event_type }}</span>
                            <span v-if="event.user" class="text-slate-400">by {{ event.user.name }}</span>
                        </div>
                        <span class="text-slate-500 font-mono text-[11px]">{{ formatDate(event.created_at) }}</span>
                    </div>

                    <!-- Changes Diff Box -->
                    <div v-if="event.changes && (event.changes.old || event.changes.new)" class="mt-2 space-y-1.5 bg-slate-950/60 rounded-lg p-2.5 border border-slate-800/60 text-xs">
                        <div
                            v-for="(newVal, field) in (event.changes.new || {})"
                            :key="field"
                            class="grid grid-cols-3 gap-2 py-0.5 text-[11px]"
                        >
                            <span class="font-semibold text-slate-400 truncate">{{ field }}:</span>
                            <span class="text-rose-400/80 line-through truncate font-mono">
                                {{ formatVal(event.changes.old?.[field]) }}
                            </span>
                            <span class="text-emerald-400 font-mono truncate">
                                {{ formatVal(newVal) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    timeline: { type: Array, default: () => [] },
});

const eventColor = (type) => {
    switch (type) {
        case 'create': return 'bg-emerald-500';
        case 'update': return 'bg-indigo-500';
        case 'delete': return 'bg-rose-500';
        case 'change_owner': return 'bg-amber-500';
        default: return 'bg-slate-500';
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatVal = (val) => {
    if (val === null || val === undefined || val === '') return 'empty';
    if (typeof val === 'boolean') return val ? 'true' : 'false';
    if (typeof val === 'object') return JSON.stringify(val);
    return String(val);
};
</script>
