export const customerMenu = {
    label: 'Customers',
    icon: 'bi bi-people',
    permission: 'customers.view',
    children: [
        { label: 'Customer list', to: { name: 'customers.index' }, permission: 'customers.view' },
        { label: 'Customer groups', to: { name: 'customers.groups' }, permission: 'customers.groups.view' },
    ],
}
