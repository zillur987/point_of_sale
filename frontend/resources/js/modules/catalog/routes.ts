import type { RouteRecordRaw } from 'vue-router'

/**
 * Spread these into the children of your authenticated layout route.
 * `meta.permission` is a hook for your existing permission guard.
 */
export const catalogRoutes: RouteRecordRaw[] = [
  {
    path: 'catalog/categories',
    name: 'catalog.categories',
    component: () => import('./pages/CategoriesPage.vue'),
    meta: { title: 'Categories', permission: 'catalog.manage' },
  },
  {
    path: 'catalog/brands',
    name: 'catalog.brands',
    component: () => import('./pages/BrandsPage.vue'),
    meta: { title: 'Brands', permission: 'catalog.manage' },
  },
  {
    path: 'catalog/units',
    name: 'catalog.units',
    component: () => import('./pages/UnitsPage.vue'),
    meta: { title: 'Units', permission: 'catalog.manage' },
  },
  {
    path: 'catalog/products',
    name: 'catalog.products',
    component: () => import('./pages/ProductsPage.vue'),
    meta: { title: 'Products', permission: 'catalog.manage' },
  },
  {
    path: 'catalog/products/create',
    name: 'catalog.products.create',
    component: () => import('./pages/ProductFormPage.vue'),
    meta: { title: 'Add product', permission: 'catalog.manage' },
  },
  {
    path: 'catalog/products/:id(\\d+)/edit',
    name: 'catalog.products.edit',
    component: () => import('./pages/ProductFormPage.vue'),
    props: true,
    meta: { title: 'Edit product', permission: 'catalog.manage' },
  },
]
