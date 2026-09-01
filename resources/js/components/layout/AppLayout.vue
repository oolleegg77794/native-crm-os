<template>
    <div class="flex h-screen w-screen overflow-hidden bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-200">
        <!-- Main Sidebar -->
        <Sidebar />

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-100/60 dark:bg-gradient-to-b dark:from-slate-950 dark:via-slate-900/60 dark:to-slate-950 transition-colors duration-200">
            <Topbar />

            <main class="flex-1 overflow-y-auto p-6">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue';
import Topbar from './Topbar.vue';
</script>
