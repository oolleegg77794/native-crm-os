<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-950 p-6 relative overflow-hidden">
        <!-- Ambient background glows -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-lg bg-slate-900/80 border border-slate-800 rounded-3xl p-8 shadow-2xl glass-panel relative z-10 space-y-6">
            <!-- Header -->
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-600/30 mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Create Organization Workspace</h2>
                <p class="text-xs text-slate-400">Launch a dedicated multi-tenant Native CRM instance</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleRegister" class="space-y-4">
                <div v-if="error" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-medium">
                    {{ error }}
                </div>

                <!-- Organization Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Company Name</label>
                        <input
                            type="text"
                            v-model="form.company_name"
                            @input="autoGenerateSlug"
                            required
                            placeholder="Acme Global"
                            class="w-full px-4 py-2.5 text-sm bg-slate-950/60 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Workspace Slug</label>
                        <input
                            type="text"
                            v-model="form.company_slug"
                            required
                            placeholder="acme-global"
                            class="w-full px-4 py-2.5 text-sm bg-slate-950/60 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition font-mono text-xs"
                        />
                    </div>
                </div>

                <!-- Administrator Info -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Your Full Name (Owner)</label>
                    <input
                        type="text"
                        v-model="form.name"
                        required
                        placeholder="Alex Morgan"
                        class="w-full px-4 py-2.5 text-sm bg-slate-950/60 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Work Email</label>
                    <input
                        type="email"
                        v-model="form.email"
                        required
                        placeholder="alex@company.com"
                        class="w-full px-4 py-2.5 text-sm bg-slate-950/60 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                    <input
                        type="password"
                        v-model="form.password"
                        required
                        minlength="8"
                        placeholder="•••••••• (Min 8 characters)"
                        class="w-full px-4 py-2.5 text-sm bg-slate-950/60 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full py-3 text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 transition disabled:opacity-50 flex items-center justify-center gap-2 mt-2"
                >
                    <svg v-if="loading" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Create Organization Workspace
                </button>
            </form>

            <div class="text-center text-xs text-slate-400 pt-2 border-t border-slate-800/80 flex items-center justify-center gap-1.5">
                <span>Already have a workspace?</span>
                <router-link to="/login" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">
                    Sign in here
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/api/client';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
    company_name: '',
    company_slug: '',
    name: '',
    email: '',
    password: '',
});

const loading = ref(false);
const error = ref('');

const autoGenerateSlug = () => {
    form.company_slug = form.company_name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '');
};

const handleRegister = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.post('/auth/register', form);
        authStore.setAuth(response.data.user, response.data.token);
        router.push({ name: 'dashboard' });
    } catch (err) {
        if (err.response?.data?.errors) {
            const firstKey = Object.keys(err.response.data.errors)[0];
            error.value = err.response.data.errors[firstKey][0];
        } else {
            error.value = err.response?.data?.message || 'Failed to create organization.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
