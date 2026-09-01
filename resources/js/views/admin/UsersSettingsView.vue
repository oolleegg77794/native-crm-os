<template>
    <div class="space-y-6 max-w-7xl mx-auto">
        <!-- Top Banner / Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    Users & Team Management
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Manage organization members, assign roles from hierarchy, and control profile permissions
                </p>
            </div>

            <button
                type="button"
                @click="openCreateModal"
                class="flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition self-start sm:self-auto"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Member</span>
            </button>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 glass-panel flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 shadow-sm">
            <div class="relative flex-1 max-w-md">
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="handleSearch"
                    placeholder="Search by name or email..."
                    class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition"
                />
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 dark:text-slate-500">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Filter by Role -->
                <select
                    v-model="filterRole"
                    @change="fetchUsers"
                    class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                >
                    <option value="">All Roles</option>
                    <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                </select>

                <!-- Filter by Profile -->
                <select
                    v-model="filterProfile"
                    @change="fetchUsers"
                    class="px-3 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                >
                    <option value="">All Profiles</option>
                    <option v-for="p in profiles" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>
        </div>

        <!-- Users Table -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 glass-panel space-y-4 shadow-sm">
            <div v-if="loading" class="py-12 text-center text-xs text-slate-400 dark:text-slate-500 animate-pulse">
                Loading team members...
            </div>

            <div v-else-if="users.length === 0" class="py-12 text-center space-y-2">
                <div class="text-slate-400 dark:text-slate-500 text-sm">No members found matching your search.</div>
                <button @click="openCreateModal" class="text-xs text-indigo-500 hover:underline font-semibold">
                    + Add your first team member
                </button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-400 dark:text-slate-500 uppercase font-semibold border-b border-slate-100 dark:border-slate-800 pb-2">
                            <th class="py-3 px-3">Member</th>
                            <th class="py-3 px-3">Role Hierarchy</th>
                            <th class="py-3 px-3">Permission Profile</th>
                            <th class="py-3 px-3">Department</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition text-slate-700 dark:text-slate-200"
                        >
                            <!-- Name & Email -->
                            <td class="py-3.5 px-3 font-semibold text-slate-900 dark:text-white flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs shadow-sm">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-semibold">{{ user.name }}</div>
                                    <div class="text-[11px] text-slate-400 font-normal">{{ user.email }}</div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td class="py-3.5 px-3">
                                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 font-medium text-xs">
                                    {{ user.role?.name || 'No Role' }}
                                </span>
                            </td>

                            <!-- Profile -->
                            <td class="py-3.5 px-3">
                                <span
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :class="user.profile?.is_admin
                                        ? 'bg-amber-500/10 text-amber-500 border border-amber-500/20'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                >
                                    {{ user.profile?.name || 'Standard' }}
                                </span>
                            </td>

                            <!-- Department -->
                            <td class="py-3.5 px-3 text-slate-500 dark:text-slate-400">
                                {{ user.department?.name || '—' }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-3 text-center">
                                <span
                                    v-if="user.is_active"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-500/10 text-rose-500 border border-rose-500/20"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEditModal(user)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="confirmDelete(user)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-900/30 text-slate-400 hover:text-rose-500 transition"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add / Edit Member Modal -->
        <Modal
            :is-open="isModalOpen"
            :title="editingUser ? 'Edit Member Card' : 'Add New Organization Member'"
            @close="closeModal"
        >
            <form @submit.prevent="saveUser" class="space-y-4">
                <div v-if="formError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs font-medium">
                    {{ formError }}
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Full Name</label>
                        <input
                            type="text"
                            v-model="userForm.name"
                            required
                            placeholder="John Doe"
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input
                            type="email"
                            v-model="userForm.email"
                            required
                            placeholder="john@company.com"
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            {{ editingUser ? 'Password (leave blank to keep current)' : 'Password' }}
                        </label>
                        <input
                            type="password"
                            v-model="userForm.password"
                            :required="!editingUser"
                            :minlength="editingUser && !userForm.password ? 0 : 8"
                            placeholder="••••••••"
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Phone (optional)</label>
                        <input
                            type="text"
                            v-model="userForm.phone"
                            placeholder="+1 (555) 000-0000"
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                        />
                    </div>
                </div>

                <!-- Role Hierarchy & Profile Selection -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Assigned Role (Hierarchy)
                        </label>
                        <select
                            v-model="userForm.role_id"
                            required
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                        >
                            <option value="" disabled>Select Role</option>
                            <option v-for="r in roles" :key="r.id" :value="r.id">
                                {{ r.name }} {{ r.parent_id ? '(Subordinate)' : '(Head)' }}
                            </option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Determines record visibility & management superiority</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Permission Profile
                        </label>
                        <select
                            v-model="userForm.profile_id"
                            required
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                        >
                            <option value="" disabled>Select Profile</option>
                            <option v-for="p in profiles" :key="p.id" :value="p.id">
                                {{ p.name }} {{ p.is_admin ? '(Full Admin)' : '' }}
                            </option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Defines CRUD privileges & Field-Level Security</p>
                    </div>
                </div>

                <!-- Status Active Checkbox -->
                <div class="flex items-center gap-2 pt-2">
                    <input
                        type="checkbox"
                        id="user_active"
                        v-model="userForm.is_active"
                        class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label for="user_active" class="text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        Account is Active (can sign in to CRM)
                    </label>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 transition disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="saving" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ editingUser ? 'Save Changes' : 'Create Member' }}
                    </button>
                </div>
            </form>
        </Modal>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '@/api/client';
import Modal from '@/components/common/Modal.vue';

const users = ref([]);
const roles = ref([]);
const profiles = ref([]);
const departments = ref([]);
const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const filterRole = ref('');
const filterProfile = ref('');

const isModalOpen = ref(false);
const editingUser = ref(null);
const formError = ref('');

const userForm = reactive({
    name: '',
    email: '',
    password: '',
    phone: '',
    role_id: '',
    profile_id: '',
    department_id: '',
    is_active: true,
});

const fetchMetadata = async () => {
    try {
        const response = await api.get('/users/meta');
        roles.value = response.data.roles || [];
        profiles.value = response.data.profiles || [];
        departments.value = response.data.departments || [];
    } catch (err) {
        console.error('Failed to load user metadata', err);
    }
};

const fetchUsers = async () => {
    loading.value = true;
    try {
        const params = {};
        if (searchQuery.value) params.search = searchQuery.value;
        if (filterRole.value) params.role_id = filterRole.value;
        if (filterProfile.value) params.profile_id = filterProfile.value;

        const response = await api.get('/users', { params });
        users.value = response.data.data || response.data || [];
    } catch (err) {
        console.error('Failed to load users', err);
    } finally {
        loading.value = false;
    }
};

let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchUsers();
    }, 300);
};

const openCreateModal = () => {
    editingUser.value = null;
    formError.value = '';
    Object.assign(userForm, {
        name: '',
        email: '',
        password: '',
        phone: '',
        role_id: roles.value[0]?.id || '',
        profile_id: profiles.value[0]?.id || '',
        department_id: '',
        is_active: true,
    });
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    formError.value = '';
    Object.assign(userForm, {
        name: user.name,
        email: user.email,
        password: '',
        phone: user.phone || '',
        role_id: user.role_id || '',
        profile_id: user.profile_id || '',
        department_id: user.department_id || '',
        is_active: Boolean(user.is_active),
    });
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingUser.value = null;
};

const saveUser = async () => {
    saving.value = true;
    formError.value = '';

    try {
        const payload = { ...userForm };
        if (editingUser.value && !payload.password) {
            delete payload.password;
        }

        if (editingUser.value) {
            await api.put(`/users/${editingUser.value.id}`, payload);
        } else {
            await api.post('/users', payload);
        }
        closeModal();
        await fetchUsers();
    } catch (err) {
        if (err.response?.data?.errors) {
            const firstKey = Object.keys(err.response.data.errors)[0];
            formError.value = err.response.data.errors[firstKey][0];
        } else {
            formError.value = err.response?.data?.message || 'Failed to save user.';
        }
    } finally {
        saving.value = false;
    }
};

const confirmDelete = async (user) => {
    if (!confirm(`Are you sure you want to delete member ${user.name}?`)) {
        return;
    }

    try {
        await api.delete(`/users/${user.id}`);
        await fetchUsers();
    } catch (err) {
        alert(err.response?.data?.message || 'Failed to delete user.');
    }
};

onMounted(async () => {
    await fetchMetadata();
    await fetchUsers();
});
</script>
