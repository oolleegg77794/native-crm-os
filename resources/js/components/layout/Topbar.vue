<template>
    <header class="h-16 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-950/70 backdrop-blur-md z-20">
        <!-- Left: Search / Breadcrumb -->
        <div class="flex items-center gap-4 flex-1 max-w-lg">
            <div class="relative w-full">
                <input
                    type="text"
                    v-model="searchQuery"
                    placeholder="Search anything in CRM (Press / to focus)..."
                    class="w-full pl-9 pr-4 py-1.5 text-xs bg-slate-900/80 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition"
                    @keydown.enter="handleSearch"
                />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Right: Actions & Badges -->
        <div class="flex items-center gap-3">
            <Badge variant="primary" :dot="true">
                {{ authStore.organization?.name || 'Production' }}
            </Badge>

            <!-- Quick Add Dropdown -->
            <Dropdown align="right" width="48">
                <template #trigger>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition"
                    >
                        <span>+ New</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </template>

                <div class="py-1">
                    <router-link
                        v-for="mod in metaStore.modules"
                        :key="mod.id"
                        :to="`/m/${mod.api_name}?action=new`"
                        class="block px-3 py-2 text-xs font-medium text-slate-200 hover:bg-indigo-600 hover:text-white rounded-lg transition"
                    >
                        New {{ mod.singular_name || mod.name }}
                    </router-link>
                </div>
            </Dropdown>
        </div>
    </header>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useMetaStore } from '@/stores/meta';
import Badge from '@/components/common/Badge.vue';
import Dropdown from '@/components/common/Dropdown.vue';

const authStore = useAuthStore();
const metaStore = useMetaStore();
const searchQuery = ref('');

const handleSearch = () => {
    // Can trigger global records search across modules
};
</script>
