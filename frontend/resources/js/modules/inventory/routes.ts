import type { RouteRecordRaw } from 'vue-router'

/**
 * Spread these into the children of your authenticated layout route.
 * `meta.permission` is a hook for your existing permission guard.
 */
export const inventoryRoutes: RouteRecordRaw[] = [
  {
    path: 'inventory/stock',
    name: 'inventory.stock',
    component: () => import('./pages/StockPage.vue'),
    meta: { title: 'Stock', permission: 'inventory.view' },
  },
  {
    path: 'inventory/movements',
    name: 'inventory.movements',
    component: () => import('./pages/StockMovementsPage.vue'),
    meta: { title: 'Stock movements', permission: 'inventory.view' },
  },
  {
    path: 'inventory/adjustments',
    name: 'inventory.adjustments',
    component: () => import('./pages/StockAdjustmentsPage.vue'),
    meta: { title: 'Stock adjustments', permission: 'inventory.view' },
  },
  {
    path: 'inventory/adjustments/create',
    name: 'inventory.adjustments.create',
    component: () => import('./pages/StockAdjustmentFormPage.vue'),
    meta: { title: 'New adjustment', permission: 'inventory.manage' },
  },
  {
    path: 'inventory/transfers',
    name: 'inventory.transfers',
    component: () => import('./pages/StockTransfersPage.vue'),
    meta: { title: 'Stock transfers', permission: 'inventory.view' },
  },
  {
    path: 'inventory/transfers/create',
    name: 'inventory.transfers.create',
    component: () => import('./pages/StockTransferFormPage.vue'),
    meta: { title: 'New transfer', permission: 'inventory.manage' },
  },
  {
    path: 'inventory/warehouses',
    name: 'inventory.warehouses',
    component: () => import('./pages/WarehousesPage.vue'),
    meta: { title: 'Warehouses', permission: 'inventory.manage' },
  },
]
