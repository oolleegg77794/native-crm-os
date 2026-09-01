<template>
    <div class="flex h-screen w-screen overflow-hidden bg-slate-950 text-slate-100">
        <!-- Main Sidebar -->
        <Sidebar />

        <!-- Main Workspace Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gradient-to-b from-slate-950 via-slate-900/60 to-slate-950">
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
