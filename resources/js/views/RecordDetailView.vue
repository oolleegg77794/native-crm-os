<template>
    <div class="space-y-6 max-w-6xl mx-auto">
        <!-- Back and Action Bar -->
        <div class="flex items-center justify-between">
            <router-link
                :to="`/m/${moduleApi}`"
                class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to {{ currentModule?.name || 'Records' }}
            </router-link>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition"
                    @click="isEditModalOpen = true"
                >
                    Edit Record
                </button>
                <button
                    type="button"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/20 transition"
                    @click="handleDelete"
                >
                    Delete
                </button>
            </div>
        </div>

        <!-- Record Title Header Card -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 glass-panel shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-xl font-bold text-lg"
                    :style="{ backgroundColor: currentModule?.color || '#6366F1' }"
                >
                    {{ (recordTitle || 'R').charAt(0) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">
                        {{ recordTitle }}
                    </h1>
                    <div class="flex items-center gap-3 text-xs text-slate-400 mt-1">
                        <span>Record ID: <span class="font-mono text-slate-300">#{{ recordId }}</span></span>
                        <span>&bull;</span>
                        <span>Owner: <span class="text-slate-300">{{ record?.owner?.name || 'Unassigned' }}</span></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Badge variant="primary" v-if="record?.is_locked">
                    Locked
                </Badge>
                <span class="text-xs text-slate-500 font-mono">
                    v{{ record?.version || 1 }}
                </span>
            </div>
        </div>

        <!-- Two Column Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Fields details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 glass-panel space-y-6">
                    <h3 class="text-sm font-semibold text-indigo-400 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                        Record Details
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="(fInfo, apiName) in (record?.fields || {})"
                            :key="apiName"
                            class="space-y-1"
                        >
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                {{ fInfo.label }}
                            </span>

                            <!-- Lookup -->
                            <div v-if="fInfo.type === 'lookup'" class="text-sm font-medium text-slate-200">
                                <span v-if="record.lookups?.[apiName]" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 text-xs font-medium">
                                    {{ record.lookups[apiName].label }}
                                </span>
                                <span v-else class="text-slate-500">—</span>
                            </div>

                            <!-- Currency -->
                            <div v-else-if="fInfo.type === 'currency'" class="text-sm font-mono font-semibold text-emerald-400">
                                ${{ Number(fInfo.value || 0).toLocaleString() }}
                            </div>

                            <!-- Picklist -->
                            <div v-else-if="fInfo.type === 'picklist'">
                                <Badge v-if="fInfo.value" variant="primary">
                                    {{ fInfo.value }}
                                </Badge>
                                <span v-else class="text-slate-500 text-sm">—</span>
                            </div>

                            <!-- Boolean -->
                            <div v-else-if="fInfo.type === 'boolean'">
                                <span
                                    class="inline-block w-3 h-3 rounded-full"
                                    :class="fInfo.value ? 'bg-emerald-400' : 'bg-slate-600'"
                                />
                            </div>

                            <!-- Default text -->
                            <div v-else class="text-sm font-medium text-slate-200">
                                {{ fInfo.value || '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Timeline -->
            <div class="space-y-6">
                <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 glass-panel">
                    <TimelineView :timeline="record?.timeline || []" />
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <RecordFormModal
            v-if="currentModule && record"
            v-model="isEditModalOpen"
            :module="currentModule"
            :is-edit="true"
            :record-id="Number(recordId)"
            :initial-data="record.raw_values"
            @saved="loadRecord"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useMetaStore } from '@/stores/meta';
import { useRecordStore } from '@/stores/records';
import TimelineView from '@/components/records/TimelineView.vue';
import RecordFormModal from '@/components/records/RecordFormModal.vue';
import Badge from '@/components/common/Badge.vue';

const props = defineProps({
    moduleApi: { type: String, required: true },
    id: { type: [String, Number], required: true },
});

const router = useRouter();
const metaStore = useMetaStore();
const recordStore = useRecordStore();

const isEditModalOpen = ref(false);
const recordId = computed(() => Number(props.id));
const currentModule = computed(() => metaStore.currentModule);
const record = computed(() => recordStore.currentRecord);

const recordTitle = computed(() => {
    if (!record.value) return '';
    const titleField = currentModule.value?.title_field || 'name';
    return record.value.raw_values?.[titleField] || `#${record.value.id}`;
});

const loadRecord = async () => {
    await metaStore.loadModule(props.moduleApi);
    await recordStore.fetchRecordById(props.moduleApi, recordId.value);
};

onMounted(loadRecord);

const handleDelete = async () => {
    if (confirm('Are you sure you want to delete this record?')) {
        await recordStore.deleteRecord(props.moduleApi, recordId.value);
        router.push(`/m/${props.moduleApi}`);
    }
};
</script>
