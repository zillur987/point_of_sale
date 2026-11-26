export const purchaseMenu={label:'Purchases',icon:'bi bi-bag-check',permission:'purchases.view',children:[
 {label:'Purchase list',to:{name:'purchases.index'},permission:'purchases.view'},
 {label:'New purchase',to:{name:'purchases.create'},permission:'purchases.create'},
 {label:'Suppliers',to:{name:'purchases.suppliers'},permission:'suppliers.view'},
]}
