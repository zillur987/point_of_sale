import type { RouteRecordRaw } from 'vue-router'

export const customerRoutes: RouteRecordRaw[] = [
    {
        path: 'customers',
        name: 'customers.index',
        component: () => import('./pages/CustomersPage.vue'),
        meta: { title: 'Customers', permission: 'customers.view' },
    },
    {
        path: 'customers/:id',
        name: 'customers.show',
        component: () => import('./pages/CustomerShowPage.vue'),
        props: true,
        meta: { title: 'Customer Profile', permission: 'customers.view' },
    },
    {
        path: 'customer-groups',
        name: 'customers.groups',
        component: () => import('./pages/CustomerGroupsPage.vue'),
        meta: { title: 'Customer Groups', permission: 'customers.groups.view' },
    },
]
