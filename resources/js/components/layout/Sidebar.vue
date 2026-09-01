<template>
    <aside
        class="w-64 h-full flex flex-col bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800/80 select-none z-30 transition-colors duration-200"
    >
        <!-- Brand / Organization Logo -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200 dark:border-slate-800/80">
            <router-link to="/" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-600/30 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div>
                    <span class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Native CRM</span>
                    <span class="block text-[11px] text-indigo-600 dark:text-indigo-400 font-medium truncate max-w-[120px]">
                        {{ authStore.organization?.name || 'Workspace' }}
                    </span>
                </div>
            </router-link>
        </div>

        <!-- Navigation Links Scroll -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
            <!-- Main Navigation -->
            <div class="space-y-1">
                <router-link
                    to="/"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition"
                    :class="$route.name === 'dashboard' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </router-link>
            </div>

            <!-- Dynamic CRM Modules Section -->
            <div class="space-y-1">
                <div class="flex items-center justify-between px-3 py-1 text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    <span>Modules</span>
                    <router-link
                        v-if="authStore.isAdmin"
                        to="/admin/modules"
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-bold"
                        title="Add Custom Module"
                    >
                        +
                    </router-link>
                </div>

                <div v-if="metaStore.loading" class="px-3 py-2 text-xs text-slate-400 dark:text-slate-500 animate-pulse">
                    Loading modules...
                </div>

                <router-link
                    v-for="mod in metaStore.modules"
                    :key="mod.id"
                    :to="`/m/${mod.api_name}`"
                    class="flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition group"
                    :class="$route.params.moduleApi === mod.api_name ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'"
                >
                    <div class="flex items-center gap-3 truncate">
                        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: mod.color || '#6366F1' }"></span>
                        <span class="truncate">{{ mod.name }}</span>
                    </div>
                </router-link>
            </div>

            <!-- Administration Section -->
            <div v-if="authStore.isAdmin" class="space-y-1">
                <div class="px-3 py-1 text-[11px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                    Administration
                </div>

                <router-link
                    to="/admin/modules"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition"
                    :class="$route.name === 'admin.modules' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Module Builder
                </router-link>

                <router-link
                    to="/admin/roles"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition"
                    :class="$route.name === 'admin.roles' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Roles & Hierarchy
                </router-link>

                <router-link
                    to="/admin/permissions"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition"
                    :class="$route.name === 'admin.permissions' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Permissions Matrix
                </router-link>

                <router-link
                    to="/admin/users"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition"
                    :class="$route.name === 'admin.users' ? 'bg-indigo-600 text-white' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Users
                </router-link>
            </div>
        </div>

        <!-- User Profile Footer -->
        <div class="p-3 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950">
            <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center gap-2.5 truncate">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                        {{ (authStore.user?.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ authStore.user?.name }}</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ authStore.profile?.name || 'User' }}</div>
                    </div>
                </div>

                <button
                    type="button"
                    class="text-slate-400 hover:text-rose-500 dark:hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    title="Sign Out"
                    @click="handleLogout"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useMetaStore } from '@/stores/meta';

const router = useRouter();
const authStore = useAuthStore();
const metaStore = useMetaStore();

onMounted(() => {
    if (authStore.isAuthenticated) {
        metaStore.fetchModules();
    }
});

const handleLogout = () => {
    authStore.logout();
    router.push({ name: 'login' });
};
</script>
