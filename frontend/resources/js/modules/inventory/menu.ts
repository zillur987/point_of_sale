/** Add this object to your sidebar menu array (same shape as catalogMenu). */
export const inventoryMenu = {
  label: 'Inventory',
  icon: 'bi bi-boxes',
  permission: 'inventory.view',
  children: [
    { label: 'Stock', to: { name: 'inventory.stock' } },
    { label: 'Movements', to: { name: 'inventory.movements' } },
    { label: 'Adjustments', to: { name: 'inventory.adjustments' } },
    { label: 'Transfers', to: { name: 'inventory.transfers' } },
    { label: 'Warehouses', to: { name: 'inventory.warehouses' }, permission: 'inventory.manage' },
  ],
}
