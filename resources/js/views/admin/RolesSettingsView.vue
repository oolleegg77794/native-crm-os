<template>
    <div class="space-y-8 max-w-7xl mx-auto">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Roles & Data Sharing Rules</h1>
            <p class="text-xs text-slate-400 mt-1">
                Configure hierarchical reporting tree and module data sharing policies (Public vs Private)
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Module Sharing Rules -->
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 glass-panel space-y-5">
                <div>
                    <h2 class="text-base font-bold text-white">Default Module Sharing Rules</h2>
                    <p class="text-xs text-slate-400">
                        Controls whether users can see records owned by others across the organization
                    </p>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="mod in adminStore.modules"
                        :key="mod.id"
                        class="p-4 rounded-xl bg-slate-950/40 border border-slate-800 flex items-center justify-between gap-4"
                    >
                        <div>
                            <span class="font-semibold text-sm text-slate-200">{{ mod.name }}</span>
                            <span class="block text-xs font-mono text-slate-500">{{ mod.api_name }}</span>
                        </div>

                        <select
                            :value="getSharingValue(mod.id)"
                            class="px-3 py-1.5 text-xs bg-slate-900 border border-slate-800 rounded-xl text-indigo-300 font-medium focus:outline-none focus:border-indigo-500 cursor-pointer"
                            @change="handleSharingChange(mod.id, $event.target.value)"
                        >
                            <option value="public_read_write" class="bg-slate-900 text-slate-200">Public Read / Write</option>
                            <option value="public_read_only" class="bg-slate-900 text-slate-200">Public Read-Only</option>
                            <option value="private" class="bg-slate-900 text-slate-200">Private (Owner & Superiors)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Right: Role Hierarchy Tree Visualizer -->
            <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 glass-panel space-y-5">
                <div>
                    <h2 class="text-base font-bold text-white">Role Hierarchy Tree</h2>
                    <p class="text-xs text-slate-400">
                        In Private modules, managers automatically inherit access to subordinate records
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-800 space-y-3 font-mono text-xs text-slate-300">
                    <div class="flex items-center gap-2 text-indigo-400 font-bold">
                        <span>👑 Chief Executive Officer (CEO)</span>
                    </div>
                    <div class="pl-6 border-l-2 border-slate-800 space-y-2">
                        <div class="flex items-center gap-2 text-emerald-400 font-semibold">
                            <span>↳ VP of Sales</span>
                        </div>
                        <div class="pl-6 border-l-2 border-slate-800 space-y-1.5 text-slate-300">
                            <div>↳ Sales Manager (US East)</div>
                            <div class="pl-4 text-slate-400">↳ Senior Sales Rep</div>
                            <div class="pl-4 text-slate-400">↳ Junior Sales Rep</div>
                            <div>↳ Sales Manager (Europe)</div>
                        </div>
                    </div>
                    <div class="pl-6 border-l-2 border-slate-800 space-y-2">
                        <div class="flex items-center gap-2 text-sky-400 font-semibold">
                            <span>↳ VP of Engineering</span>
                        </div>
                        <div class="pl-6 border-l-2 border-slate-800 text-slate-300">
                            <div>↳ Lead Architect</div>
                            <div>↳ Senior Developer</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAdminStore } from '@/stores/admin';

const adminStore = useAdminStore();

onMounted(() => {
    adminStore.fetchMatrix();
});

const getSharingValue = (moduleId) => {
    const perm = adminStore.sharingPermissions.find((s) => s.module_id === moduleId);
    return perm ? perm.access_type : 'public_read_write';
};

const handleSharingChange = async (moduleId, accessType) => {
    await adminStore.updateSharingRule(moduleId, accessType);
};
</script>
