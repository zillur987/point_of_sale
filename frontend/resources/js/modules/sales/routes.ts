import type { RouteRecordRaw } from 'vue-router'
export const salesRoutes: RouteRecordRaw[] = [
 { path:'pos', name:'sales.pos', component:()=>import('./pages/PosPage.vue'), meta:{title:'Point of Sale',permission:'sales.create'} },
 { path:'sales', name:'sales.index', component:()=>import('./pages/SalesPage.vue'), meta:{title:'Sales',permission:'sales.view'} },
]
