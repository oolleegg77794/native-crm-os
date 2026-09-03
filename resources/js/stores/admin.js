import { defineStore } from 'pinia';
import api from '@/api/client';

export const useAdminStore = defineStore('admin', {
    state: () => ({
        profiles: [],
        modules: [],
        sharingPermissions: [],
        profilePermissions: [],
        fieldPermissions: [],
        loading: false,
        saving: false,
        error: null,
    }),

    actions: {
        async fetchMatrix() {
            try {
                this.loading = true;
                const response = await api.get('/permissions/matrix');
                const data = response.data;
                this.profiles = data.profiles;
                this.modules = data.modules;
                this.sharingPermissions = data.sharing_permissions;
                this.profilePermissions = data.profile_permissions;
                this.fieldPermissions = data.field_permissions;
            } catch (err) {
                this.error = err.message;
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async updateModulePermission(profileId, moduleId, permissions) {
            const response = await api.put(`/permissions/profiles/${profileId}/modules`, {
                module_id: moduleId,
                ...permissions,
            });
            const updated = response.data.data;
            const idx = this.profilePermissions.findIndex(
                (p) => p.profile_id === profileId && p.module_id === moduleId
            );
            if (idx !== -1) {
                this.profilePermissions[idx] = updated;
            } else {
                this.profilePermissions.push(updated);
            }
            return updated;
        },

        async updateSharingRule(moduleId, accessType) {
            const response = await api.put('/permissions/sharing', {
                module_id: moduleId,
                access_type: accessType,
            });
            const updated = response.data.data;
            const idx = this.sharingPermissions.findIndex((s) => s.module_id === moduleId);
            if (idx !== -1) {
                this.sharingPermissions[idx] = updated;
            } else {
                this.sharingPermissions.push(updated);
            }
            return updated;
        },

        async updateFieldPermission(profileId, fieldId, accessLevel) {
            const response = await api.put(`/permissions/profiles/${profileId}/fields`, {
                field_id: fieldId,
                access_level: accessLevel,
            });
            const updated = response.data.data;
            const idx = this.fieldPermissions.findIndex(
                (f) => f.profile_id === profileId && f.field_id === fieldId
            );
            if (idx !== -1) {
                this.fieldPermissions[idx] = updated;
            } else {
                this.fieldPermissions.push(updated);
            }
            return updated;
        },

        async createProfile(payload) {
            const response = await api.post('/permissions/profiles', payload);
            const newProfile = response.data.data;
            this.profiles.push(newProfile);
            await this.fetchMatrix();
            return newProfile;
        },

        async updateProfile(profileId, payload) {
            const response = await api.put(`/permissions/profiles/${profileId}`, payload);
            const updated = response.data.data;
            const idx = this.profiles.findIndex((p) => p.id === profileId);
            if (idx !== -1) {
                this.profiles[idx] = updated;
            }
            return updated;
        },

        async deleteProfile(profileId) {
            await api.delete(`/permissions/profiles/${profileId}`);
            this.profiles = this.profiles.filter((p) => p.id !== profileId);
            this.profilePermissions = this.profilePermissions.filter((p) => p.profile_id !== profileId);
            this.fieldPermissions = this.fieldPermissions.filter((p) => p.profile_id !== profileId);
        },
    },
});
