import { defineStore } from 'pinia';
import api from '@/api/client';

export const useRecordStore = defineStore('records', {
    state: () => ({
        records: [],
        pagination: {
            currentPage: 1,
            lastPage: 1,
            perPage: 25,
            total: 0,
        },
        currentRecord: null,
        selectedIds: [],
        searchQuery: '',
        sortBy: 'id',
        sortDir: 'desc',
        activeFilterConditions: [],
        activeLogic: 'and',
        loading: false,
        saving: false,
        error: null,
    }),

    getters: {
        allSelected: (state) => state.records.length > 0 && state.selectedIds.length === state.records.length,
        hasSelection: (state) => state.selectedIds.length > 0,
        selectionCount: (state) => state.selectedIds.length,
    },

    actions: {
        async fetchRecords(moduleApi, page = 1) {
            try {
                this.loading = true;
                this.pagination.currentPage = page;

                const params = {
                    page,
                    per_page: this.pagination.perPage,
                    sort_by: this.sortBy,
                    sort_dir: this.sortDir,
                    search: this.searchQuery || undefined,
                };

                if (this.activeFilterConditions.length > 0) {
                    params.filters = this.activeFilterConditions;
                    params.logic = this.activeLogic;
                }

                const response = await api.get(`/modules/${moduleApi}/records`, { params });
                const data = response.data;

                this.records = data.data || [];
                this.pagination.currentPage = data.current_page;
                this.pagination.lastPage = data.last_page;
                this.pagination.total = data.total;
                this.pagination.perPage = data.per_page;

                // Clear selection if records changed
                this.selectedIds = [];

                return this.records;
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async fetchRecordById(moduleApi, id) {
            try {
                this.loading = true;
                const response = await api.get(`/modules/${moduleApi}/records/${id}`);
                this.currentRecord = response.data.data;
                return this.currentRecord;
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async createRecord(moduleApi, payload) {
            try {
                this.saving = true;
                const response = await api.post(`/modules/${moduleApi}/records`, payload);
                await this.fetchRecords(moduleApi, 1);
                return response.data.data;
            } finally {
                this.saving = false;
            }
        },

        async updateRecord(moduleApi, id, payload) {
            try {
                this.saving = true;
                const response = await api.put(`/modules/${moduleApi}/records/${id}`, payload);
                const updated = response.data.data;

                if (this.currentRecord && this.currentRecord.id === id) {
                    this.currentRecord = updated;
                }

                // Update in local records list
                const idx = this.records.findIndex((r) => r.id === id);
                if (idx !== -1) {
                    this.records[idx] = updated;
                }

                return updated;
            } finally {
                this.saving = false;
            }
        },

        async deleteRecord(moduleApi, id) {
            await api.delete(`/modules/${moduleApi}/records/${id}`);
            this.records = this.records.filter((r) => r.id !== id);
            this.selectedIds = this.selectedIds.filter((selId) => selId !== id);
            this.pagination.total = Math.max(0, this.pagination.total - 1);
        },

        async massUpdate(moduleApi, data) {
            const response = await api.post(`/modules/${moduleApi}/records/mass-update`, {
                ids: this.selectedIds,
                data,
            });
            await this.fetchRecords(moduleApi, this.pagination.currentPage);
            this.selectedIds = [];
            return response.data;
        },

        async massDelete(moduleApi) {
            const response = await api.post(`/modules/${moduleApi}/records/mass-delete`, {
                ids: this.selectedIds,
            });
            await this.fetchRecords(moduleApi, 1);
            this.selectedIds = [];
            return response.data;
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedIds = [];
            } else {
                this.selectedIds = this.records.map((r) => r.id);
            }
        },

        toggleSelect(id) {
            const index = this.selectedIds.indexOf(id);
            if (index === -1) {
                this.selectedIds.push(id);
            } else {
                this.selectedIds.splice(index, 1);
            }
        },

        setSort(sortBy) {
            if (this.sortBy === sortBy) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = sortBy;
                this.sortDir = 'asc';
            }
        },
    },
});
