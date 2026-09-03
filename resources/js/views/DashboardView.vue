<template>
    <div class="space-y-8 max-w-7xl mx-auto">
        <!-- Welcome Hero Banner -->
        <div class="p-8 rounded-3xl bg-gradient-to-r from-indigo-500/10 via-slate-100 to-purple-500/10 dark:from-indigo-900/40 dark:via-slate-900/60 dark:to-purple-900/30 border border-slate-200 dark:border-slate-800/80 glass-panel shadow-md dark:shadow-2xl relative overflow-hidden">
            <div class="relative z-10 max-w-2xl space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 dark:bg-indigo-400 animate-pulse"></span>
                    Dynamic Meta-Driven Engine
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Welcome to {{ authStore.organization?.name || 'Native CRM' }}
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    A fully customizable CRM workspace with dynamic modules, PostgreSQL JSONB storage, role-based data sharing, and field-level security.
                </p>
            </div>
        </div>

        <!-- Modules Grid -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Your Modules</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Jump directly into any dynamic entity table</p>
                </div>

                <router-link
                    v-if="authStore.isAdmin"
                    to="/admin/modules"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition flex items-center gap-1.5"
                >
                    + Build New Module
                </router-link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <router-link
                    v-for="mod in metaStore.modules"
                    :key="mod.id"
                    :to="`/m/${mod.api_name}`"
                    class="p-6 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 hover:border-indigo-500/50 hover:bg-slate-50 dark:hover:bg-slate-900/90 shadow-sm dark:shadow-md transition group glass-card space-y-4"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-lg"
                            :style="{ backgroundColor: mod.color || '#6366F1' }"
                        >
                            <span class="font-bold text-sm">{{ (mod.name || 'M').charAt(0) }}</span>
                        </div>
                        <span class="text-xs font-mono text-slate-400 dark:text-slate-500 uppercase">{{ mod.api_name }}</span>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                            {{ mod.name }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ mod.fields?.length || 0 }} configured dynamic fields
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400">
                        <span>Open Table</span>
                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useMetaStore } from '@/stores/meta';

const authStore = useAuthStore();
const metaStore = useMetaStore();

onMounted(() => {
    metaStore.fetchModules();
});
</script>
