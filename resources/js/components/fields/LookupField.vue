<template>
    <div class="relative" ref="containerRef">
        <label v-if="label" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
            {{ label }}
            <span v-if="required" class="text-rose-500 font-bold">*</span>
        </label>

        <!-- Selected Badge or Search Input -->
        <div class="relative">
            <div
                v-if="selectedLabel && !isSearching"
                class="flex items-center justify-between px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 shadow-sm"
                :class="readonly ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer hover:border-slate-300 dark:hover:border-slate-700'"
                @click="!readonly ? openSearch() : null"
            >
                <div class="flex items-center gap-2 truncate">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span class="font-medium truncate">{{ selectedLabel }}</span>
                </div>

                <button
                    v-if="!readonly"
                    type="button"
                    class="text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 p-0.5 rounded transition"
                    @click.stop="clearSelection"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div v-else class="relative">
                <input
                    ref="inputRef"
                    type="text"
                    v-model="searchTerm"
                    :disabled="readonly"
                    :placeholder="placeholder || `Search ${targetModule}...`"
                    class="w-full pl-9 pr-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 shadow-sm transition disabled:opacity-50"
                    :class="error ? 'border-rose-500 ring-1 ring-rose-500/30' : 'border-slate-200 dark:border-slate-800'"
                    @focus="isOpen = true"
                    @input="handleSearch"
                />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 dark:text-slate-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Dropdown search results -->
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-if="isOpen && !readonly"
                class="absolute z-50 mt-1 w-full max-h-60 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-1.5 shadow-2xl overflow-y-auto"
            >
                <div v-if="loading" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2">
                    <svg class="animate-spin h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Searching {{ targetModule }}...
                </div>

                <div v-else-if="results.length === 0" class="px-3 py-2 text-xs text-slate-500 dark:text-slate-400">
                    No matching records found.
                </div>

                <button
                    v-for="item in results"
                    :key="item.id"
                    type="button"
                    class="w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-600/20 hover:text-indigo-600 dark:hover:text-white flex items-center justify-between transition"
                    @click="selectItem(item)"
                >
                    <span class="truncate font-medium">{{ item.label }}</span>
                    <span class="text-xs text-slate-400 ml-2">#{{ item.id }}</span>
                </button>
            </div>
        </Transition>

        <p v-if="error" class="mt-1 text-xs text-rose-400 font-medium">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import api from '@/api/client';

const props = defineProps({
    modelValue: { type: [Number, String], default: null },
    initialLabel: { type: String, default: '' },
    label: { type: String, default: '' },
    targetModule: { type: String, required: true },
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'update:label']);

const containerRef = ref(null);
const inputRef = ref(null);
const isOpen = ref(false);
const isSearching = ref(false);
const searchTerm = ref('');
const results = ref([]);
const loading = ref(false);
const selectedLabel = ref(props.initialLabel);

watch(() => props.initialLabel, (newVal) => {
    if (newVal) selectedLabel.value = newVal;
});

let debounceTimeout = null;

const handleSearch = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(fetchResults, 250);
};

const fetchResults = async () => {
    if (!props.targetModule) return;

    try {
        loading.value = true;
        const response = await api.get(`/modules/${props.targetModule}/records`, {
            params: {
                search: searchTerm.value || undefined,
                per_page: 15,
            },
        });

        const items = response.data.data || [];
        results.value = items.map((rec) => {
            const raw = rec.raw_values || {};
            // Find title field or first string
            const label = Object.values(raw).find((v) => typeof v === 'string' && v.trim() !== '') || `#${rec.id}`;
            return { id: rec.id, label };
        });
    } catch (err) {
        results.value = [];
    } finally {
        loading.value = false;
    }
};

const openSearch = async () => {
    isSearching.value = true;
    isOpen.value = true;
    searchTerm.value = '';
    fetchResults();
    await nextTick();
    inputRef.value?.focus();
};

const selectItem = (item) => {
    selectedLabel.value = item.label;
    isSearching.value = false;
    isOpen.value = false;
    emit('update:modelValue', item.id);
    emit('update:label', item.label);
};

const clearSelection = () => {
    selectedLabel.value = '';
    emit('update:modelValue', null);
    emit('update:label', '');
};

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
        if (selectedLabel.value) {
            isSearching.value = false;
        }
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    if (!selectedLabel.value && props.modelValue) {
        // Fetch single label if id given but label missing
        api.get(`/modules/${props.targetModule}/records/${props.modelValue}`)
            .then((res) => {
                const rec = res.data.data;
                if (rec) {
                    const raw = rec.raw_values || {};
                    selectedLabel.value = Object.values(raw).find((v) => typeof v === 'string') || `#${rec.id}`;
                }
            })
            .catch(() => {});
    }
});

onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>
