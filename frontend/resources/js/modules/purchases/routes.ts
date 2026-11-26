import type { RouteRecordRaw } from 'vue-router'
export const purchaseRoutes:RouteRecordRaw[]=[
 {path:'suppliers',name:'purchases.suppliers',component:()=>import('./pages/SuppliersPage.vue'),meta:{title:'Suppliers',permission:'suppliers.view'}},
 {path:'purchases',name:'purchases.index',component:()=>import('./pages/PurchasesPage.vue'),meta:{title:'Purchases',permission:'purchases.view'}},
 {path:'purchases/create',name:'purchases.create',component:()=>import('./pages/PurchaseCreatePage.vue'),meta:{title:'New Purchase',permission:'purchases.create'}},
 {path:'purchases/:id',name:'purchases.show',component:()=>import('./pages/PurchaseShowPage.vue'),props:true,meta:{title:'Purchase Details',permission:'purchases.view'}},
]
