import { defineStore } from 'pinia';
import api from '@/api/client';

export const useMetaStore = defineStore('meta', {
    state: () => ({
        modules: [],
        currentModule: null,
        customViews: [],
        savedFilters: [],
        activeViewId: null,
        activeFilterId: null,
        loading: false,
        moduleLoading: false,
        error: null,
    }),

    getters: {
        activeFields: (state) => {
            if (!state.currentModule?.fields) return [];
            return [...state.currentModule.fields].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
        },
        currentLayout: (state) => state.currentModule?.layout || null,
        titleField: (state) => state.currentModule?.title_field || 'name',
    },

    actions: {
        async fetchModules(force = false) {
            if (this.modules.length > 0 && !force) return this.modules;

            try {
                this.loading = true;
                const response = await api.get('/modules');
                this.modules = response.data.data;
                return this.modules;
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async loadModule(apiName) {
            try {
                this.moduleLoading = true;
                const [modRes, viewsRes, filtersRes] = await Promise.all([
                    api.get(`/modules/${apiName}`),
                    api.get(`/modules/${apiName}/views`),
                    api.get(`/modules/${apiName}/filters`),
                ]);

                this.currentModule = modRes.data.data;
                this.customViews = viewsRes.data.data;
                this.savedFilters = filtersRes.data.data;

                // Set default view if available
                const defView = this.customViews.find((v) => v.is_default);
                this.activeViewId = defView ? defView.id : null;

                const defFilter = this.savedFilters.find((f) => f.is_default);
                this.activeFilterId = defFilter ? defFilter.id : null;

                return this.currentModule;
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.moduleLoading = false;
            }
        },

        async createModule(payload) {
            const response = await api.post('/modules', payload);
            const newMod = response.data.data;
            this.modules.push(newMod);
            return newMod;
        },

        async updateLayout(moduleApi, layout) {
            const response = await api.put(`/modules/${moduleApi}/layout`, { layout });
            if (this.currentModule && this.currentModule.api_name === moduleApi) {
                this.currentModule.layout = layout;
            }
            return response.data.data;
        },

        async addField(moduleApi, fieldData) {
            const response = await api.post(`/modules/${moduleApi}/fields`, fieldData);
            const newField = response.data.data;
            if (this.currentModule && this.currentModule.api_name === moduleApi) {
                this.currentModule.fields.push(newField);
            }
            return newField;
        },

        async updateField(moduleApi, fieldId, fieldData) {
            const response = await api.put(`/modules/${moduleApi}/fields/${fieldId}`, fieldData);
            const updated = response.data.data;
            if (this.currentModule && this.currentModule.api_name === moduleApi) {
                const idx = this.currentModule.fields.findIndex((f) => f.id === fieldId);
                if (idx !== -1) {
                    this.currentModule.fields[idx] = updated;
                }
            }
            return updated;
        },
    },
});
