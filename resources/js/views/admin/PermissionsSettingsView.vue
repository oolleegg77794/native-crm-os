<template>
    <div class="space-y-8 max-w-7xl mx-auto">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Permissions Matrix (3-Level Security)</h1>
            <p class="text-xs text-slate-400 mt-1">
                Configure profile permissions (CRUD per module) and Field-Level Security (Write / Read / Hidden)
            </p>
        </div>

        <!-- Profile Selector Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-800 pb-3 overflow-x-auto">
            <button
                v-for="prof in adminStore.profiles"
                :key="prof.id"
                type="button"
                class="px-4 py-2 text-xs font-semibold rounded-xl transition select-none flex items-center gap-2 whitespace-nowrap"
                :class="selectedProfileId === prof.id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'bg-slate-900/80 border border-slate-800 text-slate-300 hover:border-slate-700'"
                @click="selectedProfileId = prof.id"
            >
                <span>{{ prof.name }}</span>
                <span v-if="prof.is_admin" class="px-1.5 py-0.5 rounded text-[10px] bg-amber-500/20 text-amber-300 font-bold">
                    ADMIN
                </span>
            </button>
        </div>

        <!-- Level 1: Module CRUD Matrix -->
        <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800 glass-panel space-y-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Module Permissions (CRUD)
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-400 uppercase font-semibold border-b border-slate-800 pb-2">
                            <th class="py-2.5 px-3">Module</th>
                            <th class="py-2.5 px-3 text-center">View</th>
                            <th class="py-2.5 px-3 text-center">Create</th>
                            <th class="py-2.5 px-3 text-center">Edit</th>
                            <th class="py-2.5 px-3 text-center">Delete</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        <tr
                            v-for="mod in adminStore.modules"
                            :key="mod.id"
                            class="hover:bg-slate-800/20 transition text-slate-200"
                        >
                            <td class="py-3 px-3 font-semibold text-white">{{ mod.name }}</td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="getModulePerm(mod.id, 'can_view')"
                                    class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    @change="toggleModulePerm(mod.id, 'can_view', $event.target.checked)"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="getModulePerm(mod.id, 'can_create')"
                                    class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    @change="toggleModulePerm(mod.id, 'can_create', $event.target.checked)"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="getModulePerm(mod.id, 'can_edit')"
                                    class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    @change="toggleModulePerm(mod.id, 'can_edit', $event.target.checked)"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="getModulePerm(mod.id, 'can_delete')"
                                    class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    @change="toggleModulePerm(mod.id, 'can_delete', $event.target.checked)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useAdminStore } from '@/stores/admin';

const adminStore = useAdminStore();
const selectedProfileId = ref(null);

onMounted(async () => {
    await adminStore.fetchMatrix();
    if (adminStore.profiles.length > 0) {
        selectedProfileId.value = adminStore.profiles[0].id;
    }
});

const getModulePerm = (moduleId, action) => {
    const perm = adminStore.profilePermissions.find(
        (p) => p.profile_id === selectedProfileId.value && p.module_id === moduleId
    );
    return perm ? Boolean(perm[action]) : true;
};

const toggleModulePerm = async (moduleId, action, value) => {
    const current = adminStore.profilePermissions.find(
        (p) => p.profile_id === selectedProfileId.value && p.module_id === moduleId
    ) || {};

    const updated = {
        can_view: current.can_view ?? true,
        can_create: current.can_create ?? true,
        can_edit: current.can_edit ?? true,
        can_delete: current.can_delete ?? false,
        [action]: value,
    };

    await adminStore.updateModulePermission(selectedProfileId.value, moduleId, updated);
};
</script>
