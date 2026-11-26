
import { inventoryRoutes } from '@/modules/inventory/routes'
import { purchaseRoutes } from '@/modules/purchases/Routes'
import { salesRoutes } from '@/modules/sales/Routes'
import { catalogRoutes } from '@/modules/catalog/Routes'
import { customerRoutes } from '@/modules/customers/Routes'

// Import other module routes here.

export const moduleRoutes = [
    ...inventoryRoutes,
    ...purchaseRoutes,
    ...salesRoutes,
    ...catalogRoutes,
    ...customerRoutes,
]
