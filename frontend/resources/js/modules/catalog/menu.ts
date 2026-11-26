/** Add this object to your sidebar menu array (adjust keys to your menu component's shape). */
export const catalogMenu = {
  label: 'Catalog',
  icon: 'bi bi-box-seam',
  permission: 'catalog.manage',
  children: [
    { label: 'Products', to: { name: 'catalog.products' } },
    { label: 'Categories', to: { name: 'catalog.categories' } },
    { label: 'Brands', to: { name: 'catalog.brands' } },
    { label: 'Units', to: { name: 'catalog.units' } },
  ],
}
