import { defineStore } from 'pinia';
import api from '@/api/client';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('crm_user') || 'null'),
        token: localStorage.getItem('crm_token') || null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user,
        organization: (state) => state.user?.organization || null,
        profile: (state) => state.user?.profile || null,
        role: (state) => state.user?.role || null,
        isAdmin: (state) => Boolean(state.user?.profile?.is_admin),
    },

    actions: {
        setAuth(user, token) {
            this.user = user;
            this.token = token;
            localStorage.setItem('crm_user', JSON.stringify(user));
            localStorage.setItem('crm_token', token);
        },

        async fetchCurrentUser() {
            try {
                this.loading = true;
                const response = await api.get('/user', { baseURL: '/api' });
                this.user = response.data;
                localStorage.setItem('crm_user', JSON.stringify(this.user));
                return this.user;
            } catch (err) {
                this.logout();
                throw err;
            } finally {
                this.loading = false;
            }
        },

        logout() {
            this.user = null;
            this.token = null;
            localStorage.removeItem('crm_user');
            localStorage.removeItem('crm_token');
        },

        /**
         * Check if current user can perform an action on a module
         */
        can(moduleApi, action = 'view') {
            if (this.isAdmin) return true;
            // Additional profile permissions check can be evaluated here or delegated to backend
            return true;
        },
    },
});
