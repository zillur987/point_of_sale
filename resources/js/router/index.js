
import { createRouter, createWebHistory } from 'vue-router'

import { useAuthStore } from '@/stores/auth'
import { usePermissionStore } from '@/stores/permissions'

import AppLayout from '@/layouts/AppLayout.vue'

const RoleList = () => import('@/views/roles/RoleListView.vue')
const UserAccessList = () => import('@/views/users/UserAccessView.vue')
const Forbidden = () => import('@/views/errors/ForbiddenView.vue')

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { guest: true },
    },

    {
        path: '/',
        component: AppLayout,
        meta: { requiresAuth: true },

        children: [
            // Default landing route
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/views/DashboardView.vue'),
                meta: {
                    title: 'Dashboard',
                    requiresAuth: true,
                },
            },

            // Roles
            {
                path: 'roles',
                name: 'roles.index',
                component: RoleList,
                meta: { permission: 'roles.view' },
            },
            {
                path: 'roles/new',
                name: 'roles.create',
                component: RoleList,
                meta: { permission: 'roles.create' },
            },
            {
                path: 'roles/:id',
                name: 'roles.show',
                component: RoleList,
                meta: { permission: 'roles.view' },
            },
            {
                path: 'roles/:id/edit',
                name: 'roles.edit',
                component: RoleList,
                meta: { permission: 'roles.edit' },
            },

            // User access
            {
                path: 'access/users',
                name: 'access.users',
                component: UserAccessList,
                meta: { permission: 'users.view' },
            },
            {
                path: 'access/users/new',
                name: 'access.users.create',
                component: UserAccessList,
                meta: { permission: 'users.create' },
            },
            {
                path: 'access/users/:id/roles',
                name: 'access.users.edit',
                component: UserAccessList,
                meta: { permission: 'users.edit' },
            },

            // Forbidden
            {
                path: 'forbidden',
                name: 'forbidden',
                component: Forbidden,
            },
        ],
    },

    // Unknown routes
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
]

const router = createRouter({
    history: createWebHistory('/backend/'),
    routes,
})

// Find the first accessible page
function firstAllowedRoute(access) {
    // Dashboard is the default landing page.
    return { name: 'dashboard' };
}

// Navigation guard
router.beforeEach(async (to) => {
    const auth = useAuthStore();

    // Restore authentication before checking protected routes.
    await auth.bootstrap();

    const requiresAuth = to.matched.some(
        (route) => route.meta.requiresAuth
    );

    // Authentication validation
    if (requiresAuth && !auth.isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath },
        };
    }

    // Guest-only routes
    if (to.meta.guest && auth.isAuthenticated) {
        const access = usePermissionStore();

        try {
            await access.load();
            return firstAllowedRoute(access);
        } catch (error) {
            console.error('Permission loading failed:', error);

            return { name: 'forbidden' };
        }
    }

    // Public routes
    if (!requiresAuth) {
        return true;
    }

    const access = usePermissionStore();

    try {
        await access.load();
    } catch (error) {
        console.error('Permission loading failed:', error);

        return { name: 'forbidden' };
    }

    // Default landing page
    if (to.name === 'dashboard') {
        return true;
    }

    // Permission validation
    const permission = to.meta.permission;

    if (!permission) {
        return true;
    }

    if (access.canAny(permission)) {
        return true;
    }

    return {
        name: 'forbidden',
        query: { from: to.fullPath },
    };
});

export default router
