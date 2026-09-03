<template>
    <div class="space-y-8 max-w-7xl mx-auto">
        <!-- Top Title & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Permissions Matrix (3-Level Security)</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Manage security profiles, module CRUD privileges, and granular Field-Level Security (Write / Read / Hidden)
                </p>
            </div>

            <button
                type="button"
                class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition flex items-center gap-2 self-start sm:self-auto cursor-pointer"
                @click="openCreateProfileModal"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Create New Profile</span>
            </button>
        </div>

        <!-- Profile Selector Tabs & Management Toolbar -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 glass-panel shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <!-- Profiles Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 flex-1">
                    <button
                        v-for="prof in adminStore.profiles"
                        :key="prof.id"
                        type="button"
                        class="px-4 py-2 text-xs font-semibold rounded-xl transition select-none flex items-center gap-2 whitespace-nowrap shadow-sm cursor-pointer"
                        :class="selectedProfileId === prof.id ? 'bg-indigo-600 text-white shadow-indigo-600/25' : 'bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:border-slate-700'"
                        @click="selectedProfileId = prof.id"
                    >
                        <span>{{ prof.name }}</span>
                        <span v-if="prof.is_admin" class="px-1.5 py-0.5 rounded text-[10px] bg-amber-500/20 text-amber-300 font-bold border border-amber-500/30">
                            ADMIN
                        </span>
                    </button>
                </div>

                <!-- Profile Actions (Edit/Delete) -->
                <div v-if="selectedProfile && !selectedProfile.is_admin" class="flex items-center gap-2 self-end md:self-auto">
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition cursor-pointer"
                        @click="openEditProfileModal"
                    >
                        Edit Profile
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 transition cursor-pointer"
                        @click="handleDeleteProfile"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div v-if="selectedProfile?.description" class="text-xs text-slate-500 dark:text-slate-400 pl-1">
                {{ selectedProfile.description }}
            </div>
        </div>

        <!-- Level 1: Module CRUD Matrix -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 glass-panel shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    1. Module Permissions (CRUD)
                </h2>
                <span class="text-xs text-slate-500 font-medium">Profile: <strong class="text-indigo-600 dark:text-indigo-400">{{ selectedProfile?.name }}</strong></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-500 dark:text-slate-400 uppercase font-semibold border-b border-slate-200 dark:border-slate-800 pb-2">
                            <th class="py-2.5 px-3">Module</th>
                            <th class="py-2.5 px-3 text-center">View</th>
                            <th class="py-2.5 px-3 text-center">Create</th>
                            <th class="py-2.5 px-3 text-center">Edit</th>
                            <th class="py-2.5 px-3 text-center">Delete</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        <tr
                            v-for="mod in adminStore.modules"
                            :key="mod.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition text-slate-800 dark:text-slate-200"
                        >
                            <td class="py-3 px-3 font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: mod.color || '#6366F1' }"></span>
                                <span>{{ mod.name }}</span>
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="getModulePerm(mod.id, 'can_view')"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    @change="toggleModulePerm(mod.id, 'can_view', $event.target.checked)"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="getModulePerm(mod.id, 'can_create')"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    @change="toggleModulePerm(mod.id, 'can_create', $event.target.checked)"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="getModulePerm(mod.id, 'can_edit')"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    @change="toggleModulePerm(mod.id, 'can_edit', $event.target.checked)"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    :checked="getModulePerm(mod.id, 'can_delete')"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                    @change="toggleModulePerm(mod.id, 'can_delete', $event.target.checked)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Level 2: Field-Level Security (FLS) Matrix -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 glass-panel shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        2. Field-Level Security (FLS)
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Configure Read/Write/Hidden permissions per field for profile <span class="font-bold text-slate-700 dark:text-slate-300">"{{ selectedProfile?.name }}"</span>
                    </p>
                </div>

                <!-- Module Switcher & Presets for FLS -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Module:</label>
                        <select
                            v-model="selectedFlsModuleId"
                            class="px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500/50 cursor-pointer shadow-sm"
                        >
                            <option v-for="m in adminStore.modules" :key="m.id" :value="m.id">
                                {{ m.name }} ({{ m.fields?.length || 0 }} fields)
                            </option>
                        </select>
                    </div>

                    <!-- Batch Presets -->
                    <div class="flex items-center gap-1.5 border-l border-slate-200 dark:border-slate-800 pl-3">
                        <button
                            type="button"
                            class="px-2 py-1 text-[11px] font-semibold rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 transition cursor-pointer"
                            @click="setAllFieldsPerm('write')"
                            title="Set all fields in this module to Write"
                        >
                            All Write
                        </button>
                        <button
                            type="button"
                            class="px-2 py-1 text-[11px] font-semibold rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 border border-amber-500/20 transition cursor-pointer"
                            @click="setAllFieldsPerm('read')"
                            title="Set all fields in this module to Read Only"
                        >
                            All Read
                        </button>
                        <button
                            type="button"
                            class="px-2 py-1 text-[11px] font-semibold rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 border border-rose-500/20 transition cursor-pointer"
                            @click="setAllFieldsPerm('hidden')"
                            title="Set all fields in this module to Hidden"
                        >
                            All Hidden
                        </button>
                    </div>
                </div>
            </div>

            <!-- Fields List for Selected Module -->
            <div v-if="currentFlsModule" class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-500 dark:text-slate-400 uppercase font-semibold border-b border-slate-200 dark:border-slate-800 pb-2">
                            <th class="py-2.5 px-3">Field Label</th>
                            <th class="py-2.5 px-3">API Name</th>
                            <th class="py-2.5 px-3">Type</th>
                            <th class="py-2.5 px-3 text-right">Access Permission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        <tr
                            v-for="field in (currentFlsModule.fields || [])"
                            :key="field.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition text-slate-800 dark:text-slate-200"
                        >
                            <td class="py-3.5 px-3 font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                <span>{{ field.label }}</span>
                                <span v-if="field.is_required" class="text-[10px] text-rose-500 font-bold">*Required</span>
                            </td>
                            <td class="py-3.5 px-3 font-mono text-slate-500 dark:text-slate-400">{{ field.api_name }}</td>
                            <td class="py-3.5 px-3 font-mono text-indigo-600 dark:text-indigo-400">{{ field.type }}</td>

                            <!-- Access Level Radio Buttons -->
                            <td class="py-3.5 px-3 text-right">
                                <div class="inline-flex items-center gap-1.5 p-1 rounded-xl bg-slate-100 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 shadow-inner">
                                    <!-- Write Option -->
                                    <button
                                        type="button"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer"
                                        :class="getFieldPerm(field.id) === 'write'
                                            ? 'bg-emerald-500 text-white shadow-sm'
                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                                        @click="setFieldPerm(field.id, 'write')"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="getFieldPerm(field.id) === 'write' ? 'bg-white' : 'bg-emerald-500'"></span>
                                        Write
                                    </button>

                                    <!-- Read Only Option -->
                                    <button
                                        type="button"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer"
                                        :class="getFieldPerm(field.id) === 'read'
                                            ? 'bg-amber-500 text-white shadow-sm'
                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                                        @click="setFieldPerm(field.id, 'read')"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="getFieldPerm(field.id) === 'read' ? 'bg-white' : 'bg-amber-500'"></span>
                                        Read
                                    </button>

                                    <!-- Don't Show / Hidden Option -->
                                    <button
                                        type="button"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer"
                                        :class="getFieldPerm(field.id) === 'hidden'
                                            ? 'bg-rose-500 text-white shadow-sm'
                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                                        @click="setFieldPerm(field.id, 'hidden')"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="getFieldPerm(field.id) === 'hidden' ? 'bg-white' : 'bg-rose-500'"></span>
                                        Don't Show
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="py-8 text-center text-slate-400 text-xs">
                No fields available for the selected module.
            </div>
        </div>

        <!-- Create / Edit Profile Modal -->
        <Modal
            v-model="isProfileModalOpen"
            :title="isEditProfile ? 'Edit Profile' : 'Create Security Profile'"
            :subtitle="isEditProfile ? 'Update profile name and description' : 'Define a new user profile with custom CRUD & field permissions'"
            size="md"
        >
            <form @submit.prevent="handleSaveProfile" id="profile-form" class="space-y-4">
                <div v-if="profileError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs font-medium">
                    {{ profileError }}
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Profile Name</label>
                    <input
                        type="text"
                        v-model="profileForm.name"
                        required
                        placeholder="e.g. Sales Representative, Finance Manager"
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                    <textarea
                        v-model="profileForm.description"
                        rows="2"
                        placeholder="Describe the scope of access for this profile..."
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm"
                    />
                </div>

                <!-- Copy From Existing Profile (Only on Create) -->
                <div v-if="!isEditProfile">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Clone Permissions From (Optional)</label>
                    <select
                        v-model="profileForm.copy_from_profile_id"
                        class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 shadow-sm"
                    >
                        <option :value="null">Start fresh with default permissions</option>
                        <option v-for="p in adminStore.profiles" :key="p.id" :value="p.id">
                            Copy from {{ p.name }}
                        </option>
                    </select>
                </div>
            </form>

            <template #footer>
                <button
                    type="button"
                    class="px-4 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition shadow-sm cursor-pointer"
                    @click="isProfileModalOpen = false"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    form="profile-form"
                    :disabled="isProfileSaving"
                    class="px-5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition disabled:opacity-50 flex items-center gap-2 cursor-pointer"
                >
                    <svg v-if="isProfileSaving" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ isEditProfile ? 'Update Profile' : 'Create Profile' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { useAdminStore } from '@/stores/admin';
import Modal from '@/components/common/Modal.vue';

const adminStore = useAdminStore();
const selectedProfileId = ref(null);
const selectedFlsModuleId = ref(null);

const isProfileModalOpen = ref(false);
const isEditProfile = ref(false);
const isProfileSaving = ref(false);
const profileError = ref('');

const profileForm = reactive({
    name: '',
    description: '',
    copy_from_profile_id: null,
});

onMounted(async () => {
    await adminStore.fetchMatrix();
    if (adminStore.profiles.length > 0) {
        selectedProfileId.value = adminStore.profiles[0].id;
    }
    if (adminStore.modules.length > 0) {
        selectedFlsModuleId.value = adminStore.modules[0].id;
    }
});

const selectedProfile = computed(() => {
    return adminStore.profiles.find((p) => p.id === selectedProfileId.value) || null;
});

const currentFlsModule = computed(() => {
    return adminStore.modules.find((m) => m.id === selectedFlsModuleId.value) || null;
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

const getFieldPerm = (fieldId) => {
    const perm = adminStore.fieldPermissions.find(
        (f) => f.profile_id === selectedProfileId.value && f.field_id === fieldId
    );
    return perm ? perm.access_level : 'write';
};

const setFieldPerm = async (fieldId, accessLevel) => {
    if (!selectedProfileId.value) return;
    await adminStore.updateFieldPermission(selectedProfileId.value, fieldId, accessLevel);
};

const setAllFieldsPerm = async (accessLevel) => {
    if (!selectedProfileId.value || !currentFlsModule.value) return;
    const fields = currentFlsModule.value.fields || [];
    for (const field of fields) {
        await adminStore.updateFieldPermission(selectedProfileId.value, field.id, accessLevel);
    }
};

const openCreateProfileModal = () => {
    isEditProfile.value = false;
    profileError.value = '';
    Object.assign(profileForm, {
        name: '',
        description: '',
        copy_from_profile_id: selectedProfileId.value || null,
    });
    isProfileModalOpen.value = true;
};

const openEditProfileModal = () => {
    if (!selectedProfile.value) return;
    isEditProfile.value = true;
    profileError.value = '';
    Object.assign(profileForm, {
        name: selectedProfile.value.name,
        description: selectedProfile.value.description || '',
        copy_from_profile_id: null,
    });
    isProfileModalOpen.value = true;
};

const handleSaveProfile = async () => {
    isProfileSaving.value = true;
    profileError.value = '';

    try {
        if (isEditProfile.value) {
            await adminStore.updateProfile(selectedProfileId.value, {
                name: profileForm.name,
                description: profileForm.description,
            });
        } else {
            const newProf = await adminStore.createProfile({
                name: profileForm.name,
                description: profileForm.description,
                copy_from_profile_id: profileForm.copy_from_profile_id,
            });
            selectedProfileId.value = newProf.id;
        }
        isProfileModalOpen.value = false;
    } catch (err) {
        profileError.value = err.response?.data?.message || 'Failed to save profile.';
    } finally {
        isProfileSaving.value = false;
    }
};

const handleDeleteProfile = async () => {
    if (!selectedProfile.value) return;
    if (!confirm(`Are you sure you want to delete profile "${selectedProfile.value.name}"?`)) {
        return;
    }

    try {
        await adminStore.deleteProfile(selectedProfileId.value);
        if (adminStore.profiles.length > 0) {
            selectedProfileId.value = adminStore.profiles[0].id;
        }
    } catch (err) {
        alert(err.response?.data?.message || 'Failed to delete profile.');
    }
};
</script>
