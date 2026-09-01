<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-950 p-6 relative overflow-hidden">
        <!-- Ambient background glows -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-slate-900/80 border border-slate-800 rounded-3xl p-8 shadow-2xl glass-panel relative z-10 space-y-6">
            <!-- Header -->
            <div class="text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-600/30 mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Sign in to Native CRM</h2>
                <p class="text-xs text-slate-400">Access your organization's dynamic workspace</p>
            </div>

            <!-- Credentials Hint Card -->
            <div class="p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 space-y-1 text-xs">
                <div class="font-semibold text-indigo-300">Default Demo Credentials:</div>
                <div class="text-slate-300 flex justify-between">
                    <span>Email: <code class="text-indigo-400 font-mono">admin@crm.local</code></span>
                    <span>Pass: <code class="text-indigo-400 font-mono">password</code></span>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleLogin" class="space-y-4">
                <div v-if="error" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-medium">
                    {{ error }}
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email</label>
                    <input
                        type="email"
                        v-model="email"
                        required
                        placeholder="admin@crm.local"
                        class="w-full px-4 py-2.5 text-sm bg-slate-950/60 border border-slate-800 rounded-xl text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                    <input
                        type="password"
                        v-model="password"
                        required
                        placeholder="••••••••"
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
                    Sign In
                </button>
            </form>

            <div class="text-center text-xs text-slate-500 pt-2 border-t border-slate-800/80">
                Native Multi-Tenant Architecture &bull; PostgreSQL JSONB
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/api/client';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('admin@crm.local');
const password = ref('password');
const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.post('/auth/login', {
            email: email.value,
            password: password.value,
        });

        authStore.setAuth(response.data.user, response.data.token);
        router.push({ name: 'dashboard' });
    } catch (err) {
        if (err.response?.data?.errors?.email) {
            error.value = err.response.data.errors.email[0];
        } else {
            error.value = err.response?.data?.message || 'Invalid credentials.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
