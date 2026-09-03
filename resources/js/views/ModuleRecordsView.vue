<template>
    <div class="space-y-6 max-w-7xl mx-auto">
        <!-- Module Header & Toolbar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Left: Module info & Custom Views -->
            <div class="flex items-center gap-4">
                <div
                    class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shadow-xl"
                    :style="{ backgroundColor: currentModule?.color || '#6366F1' }"
                >
                    <span class="font-bold text-base">{{ (currentModule?.name || 'M').charAt(0) }}</span>
                </div>

                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ currentModule?.name || 'Loading...' }}
                        </h1>
                        <Badge variant="primary" v-if="recordStore.pagination.total > 0">
                            {{ recordStore.pagination.total }} records
                        </Badge>
                    </div>

                    <!-- Custom Views Selector -->
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-slate-500 dark:text-slate-400">View:</span>
                        <select
                            v-model="activeViewApi"
                            class="bg-transparent text-xs font-semibold text-indigo-600 dark:text-indigo-400 focus:outline-none cursor-pointer hover:underline"
                            @change="handleViewChange"
                        >
                            <option value="" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">Default View</option>
                            <option
                                v-for="view in metaStore.customViews"
                                :key="view.id"
                                :value="view.id"
                                class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                            >
                                {{ view.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Right: Actions Toolbar -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Search Input -->
                <div class="relative">
                    <input
                        type="text"
                        v-model="searchTerm"
                        placeholder="Search records..."
                        class="w-48 md:w-64 pl-8 pr-3 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition"
                        @input="handleSearch"
                    />
                    <div class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400 dark:text-slate-500">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Columns Customizer Button -->
                <button
                    type="button"
                    class="px-3 py-1.5 text-xs font-medium rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/80 text-slate-700 dark:text-slate-300 shadow-sm transition flex items-center gap-1.5"
                    @click="isColumnsModalOpen = true"
                >
                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    Columns
                </button>

                <!-- Filters Drawer Button -->
                <button
                    type="button"
                    class="px-3 py-1.5 text-xs font-medium rounded-xl border shadow-sm transition flex items-center gap-1.5"
                    :class="recordStore.activeFilterConditions.length > 0 ? 'border-indigo-500 text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/20' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80'"
                    @click="isFiltersDrawerOpen = true"
                >
                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                    <span v-if="recordStore.activeFilterConditions.length > 0" class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                </button>

                <!-- Add Record Primary Button -->
                <button
                    type="button"
                    class="px-4 py-1.5 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/25 transition flex items-center gap-1.5"
                    @click="openCreateModal"
                >
                    + New {{ currentModule?.singular_name || currentModule?.name || 'Record' }}
                </button>
            </div>
        </div>

        <!-- Dynamic Records Table -->
        <RecordTable
            v-if="currentModule"
            :module="currentModule"
            :records="recordStore.records"
            :selected-columns="customColumns"
            :selected-ids="recordStore.selectedIds"
            :all-selected="recordStore.allSelected"
            :sort-by="recordStore.sortBy"
            :sort-dir="recordStore.sortDir"
            :loading="recordStore.loading"
            @toggle-select-all="recordStore.toggleSelectAll"
            @toggle-select="recordStore.toggleSelect"
            @sort="handleSort"
            @view="goToDetail"
            @edit="openEditModal"
            @delete="handleDelete"
            @create="openCreateModal"
        />

        <!-- Pagination -->
        <Pagination
            v-if="recordStore.pagination.total > 0"
            :current-page="recordStore.pagination.currentPage"
            :last-page="recordStore.pagination.lastPage"
            :per-page="recordStore.pagination.perPage"
            :total="recordStore.pagination.total"
            @change-page="recordStore.fetchRecords(moduleApi, $event)"
        />

        <!-- Modals & Drawers -->
        <RecordFormModal
            v-if="currentModule"
            v-model="isFormModalOpen"
            :module="currentModule"
            :is-edit="isEditMode"
            :record-id="editingRecordId"
            :initial-data="editingRecordData"
            @saved="handleRecordSaved"
        />

        <CustomViewModal
            v-if="currentModule"
            v-model="isColumnsModalOpen"
            :module="currentModule"
            :current-columns="customColumns"
            @save="customColumns = $event"
        />

        <SavedFiltersDrawer
            v-if="currentModule"
            v-model="isFiltersDrawerOpen"
            :module="currentModule"
            @apply="handleApplyFilters"
        />

        <MassActionsBar
            :count="recordStore.selectionCount"
            @mass-update="isMassUpdateModalOpen = true"
            @mass-delete="handleMassDelete"
            @clear="recordStore.selectedIds = []"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useMetaStore } from '@/stores/meta';
import { useRecordStore } from '@/stores/records';
import RecordTable from '@/components/records/RecordTable.vue';
import RecordFormModal from '@/components/records/RecordFormModal.vue';
import CustomViewModal from '@/components/records/CustomViewModal.vue';
import SavedFiltersDrawer from '@/components/records/SavedFiltersDrawer.vue';
import MassActionsBar from '@/components/records/MassActionsBar.vue';
import Pagination from '@/components/common/Pagination.vue';
import Badge from '@/components/common/Badge.vue';

const props = defineProps({
    moduleApi: { type: String, required: true },
});

const router = useRouter();
const route = useRoute();
const metaStore = useMetaStore();
const recordStore = useRecordStore();

const searchTerm = ref('');
const activeViewApi = ref('');
const customColumns = ref(null);
const isFormModalOpen = ref(false);
const isColumnsModalOpen = ref(false);
const isFiltersDrawerOpen = ref(false);
const isMassUpdateModalOpen = ref(false);
const isEditMode = ref(false);
const editingRecordId = ref(null);
const editingRecordData = ref({});

const currentModule = computed(() => metaStore.currentModule);

let debounceTimer = null;

const loadModuleData = async () => {
    await metaStore.loadModule(props.moduleApi);
    await recordStore.fetchRecords(props.moduleApi, 1);

    if (route.query.action === 'new') {
        openCreateModal();
    }
};

watch(() => props.moduleApi, () => {
    customColumns.value = null;
    loadModuleData();
});

onMounted(loadModuleData);

const handleSearch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        recordStore.searchQuery = searchTerm.value;
        recordStore.fetchRecords(props.moduleApi, 1);
    }, 300);
};

const handleSort = (fieldApi) => {
    recordStore.setSort(fieldApi);
    recordStore.fetchRecords(props.moduleApi, recordStore.pagination.currentPage);
};

const handleViewChange = () => {
    if (!activeViewApi.value) {
        customColumns.value = null;
    } else {
        const view = metaStore.customViews.find((v) => v.id === activeViewApi.value);
        if (view && view.columns) {
            customColumns.value = view.columns;
        }
    }
};

const handleApplyFilters = ({ conditions, logic }) => {
    recordStore.activeFilterConditions = conditions;
    recordStore.activeLogic = logic;
    recordStore.fetchRecords(props.moduleApi, 1);
};

const openCreateModal = () => {
    isEditMode.value = false;
    editingRecordId.value = null;
    editingRecordData.value = {};
    isFormModalOpen.value = true;
};

const openEditModal = (record) => {
    isEditMode.value = true;
    editingRecordId.value = record.id;
    editingRecordData.value = record.raw_values || {};
    isFormModalOpen.value = true;
};

const goToDetail = (id) => {
    router.push({ name: 'module.record.detail', params: { moduleApi: props.moduleApi, id } });
};

const handleDelete = async (id) => {
    if (confirm('Are you sure you want to delete this record?')) {
        await recordStore.deleteRecord(props.moduleApi, id);
    }
};

const handleMassDelete = async () => {
    if (confirm(`Are you sure you want to delete ${recordStore.selectionCount} selected records?`)) {
        await recordStore.massDelete(props.moduleApi);
    }
};

const handleRecordSaved = () => {
    recordStore.fetchRecords(props.moduleApi, recordStore.pagination.currentPage);
};
</script>
