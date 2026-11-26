export const salesMenu = {
 label:'Sales', icon:'bi bi-receipt', permission:'sales.view',
 children:[
  {label:'Point of Sale',to:{name:'sales.pos'},permission:'sales.create'},
  {label:'Sales history',to:{name:'sales.index'},permission:'sales.view'},
 ],
}
