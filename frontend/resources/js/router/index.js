
import { createRouter, createWebHistory } from 'vue-router'

import { useAuthStore } from '@/stores/auth'
import { usePermissionStore } from '@/stores/permissions'

import AppLayout from '@/layouts/AppLayout.vue'

import { moduleRoutes } from './moduleRoutes'

const RoleList = () => import('@/views/roles/RoleListView.vue')
const UserAccessList = () => import('@/views/users/UserAccessView.vue')
const Forbidden = () => import('@/views/errors/ForbiddenView.vue')
const Dashboard = () => import('@/views/DashboardView.vue')

const routes = [
    // Guest routes
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { guest: true },
    },

    // Authenticated application
    {
        path: '/',
        component: AppLayout,
        meta: { requiresAuth: true },

        children: [
            // Dashboard
            {
                path: '',
                name: 'dashboard',
                component: Dashboard,
                meta: {
                    title: 'Dashboard',
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

            // All registered module routes
            ...moduleRoutes,

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

// Find an accessible landing page
async function firstAllowedRoute(access) {
    if (access.canAny('dashboard.view')) {
        return { name: 'dashboard' }
    }

    for (const route of router.getRoutes()) {
        if (
            route.name &&
            route.meta?.permission &&
            access.canAny(route.meta.permission) &&
            route.name !== 'forbidden'
        ) {
            return { name: route.name }
        }
    }

    return { name: 'forbidden' }
}

// Navigation guard
router.beforeEach(async (to) => {
    const auth = useAuthStore()

    // Restore authentication
    await auth.bootstrap()

    const requiresAuth = to.matched.some(
        route => route.meta.requiresAuth
    )

    // Authentication validation
    if (requiresAuth && !auth.isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath },
        }
    }

    // Authenticated users cannot access guest-only pages
    if (to.meta.guest && auth.isAuthenticated) {
        const access = usePermissionStore()

        try {
            await access.load()
            return firstAllowedRoute(access)
        } catch (error) {
            console.error('Permission loading failed:', error)
            return { name: 'forbidden' }
        }
    }

    // Public routes
    if (!requiresAuth) {
        return true
    }

    const access = usePermissionStore()

    // Load permissions
    try {
        await access.load()
    } catch (error) {
        console.error('Permission loading failed:', error)
        return { name: 'forbidden' }
    }

    // Forbidden page must remain accessible
    if (to.name === 'forbidden') {
        return true
    }

    // Dashboard access
    if (to.name === 'dashboard') {
        return true
    }

    // Check all matched route permissions
    const requiredPermissions = to.matched
        .map(route => route.meta.permission)
        .filter(Boolean)

    if (
        requiredPermissions.length === 0 ||
        access.canAny(requiredPermissions)
    ) {
        return true
    }

    return {
        name: 'forbidden',
        query: { from: to.fullPath },
    }
})

export default router
