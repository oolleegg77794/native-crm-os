import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/RegisterView.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('@/components/layout/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/views/DashboardView.vue'),
            },
            {
                path: 'm/:moduleApi',
                name: 'module.records',
                component: () => import('@/views/ModuleRecordsView.vue'),
                props: true,
            },
            {
                path: 'm/:moduleApi/:id',
                name: 'module.record.detail',
                component: () => import('@/views/RecordDetailView.vue'),
                props: true,
            },
            {
                path: 'admin/modules',
                name: 'admin.modules',
                component: () => import('@/views/admin/ModulesSettingsView.vue'),
                meta: { requiresAdmin: true },
            },
            {
                path: 'admin/roles',
                name: 'admin.roles',
                component: () => import('@/views/admin/RolesSettingsView.vue'),
                meta: { requiresAdmin: true },
            },
            {
                path: 'admin/permissions',
                name: 'admin.permissions',
                component: () => import('@/views/admin/PermissionsSettingsView.vue'),
                meta: { requiresAdmin: true },
            },
            {
                path: 'admin/users',
                name: 'admin.users',
                component: () => import('@/views/admin/UsersSettingsView.vue'),
                meta: { requiresAdmin: true },
            },
        ],
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next({ name: 'login' });
    }

    if (to.meta.guest && authStore.isAuthenticated) {
        return next({ name: 'dashboard' });
    }

    if (to.meta.requiresAdmin && !authStore.isAdmin) {
        return next({ name: 'dashboard' });
    }

    next();
});

export default router;
