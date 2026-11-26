# POS System — Step 6: Customer Management & Loyalty

Additive Laravel + Vue 3 + TypeScript module, written to follow the uploaded Step 5 Suppliers & Purchase Management package.

## Included
- Customer directory with search, group/status filters, pagination, create and edit.
- Customer profile with contact details and loyalty balance.
- Customer groups with create/edit and activation status.
- Loyalty transaction history.
- Earn, redeem and manual adjustment of loyalty points.
- Transaction-safe points updates with row locking; redemption cannot make the balance negative.
- Laravel Form Requests, API Resources, repository interfaces/Eloquent repositories, services and provider bindings.
- Vue API client, module menu, module routes and pages.

## Scope / compatibility boundary
The uploaded archive contains Step 5 only, not the complete POS project. This module follows the conventions visible in Step 5:
- Laravel 11/12, PHP 8.2+, Vue 3 + TypeScript.
- Existing authenticated `/api/v1` route group.
- Existing `App\Models\User`.
- Existing frontend `@/api/http` axios client and `Paginated` type from `@/api/catalog`.
- Existing `createWebHistory('/backend/')` router and modular `menu.ts` / `routes.ts` registration.
- Money is not used in this module. Loyalty points are integer units.

This package does not assume a sales table/model because the uploaded Step 5 package does not include the Step 4 sales schema. Therefore, automatic points earning from completed sales and purchase-history totals are not included. To enable automatic earning, the Step 4 sale completion workflow must call the loyalty service after its sale transaction is committed, and the sale/customer relationship must be added using the actual sales schema.

## Backend installation
1. Merge `backend/app`, `backend/database/migrations`, and `backend/routes` into the Laravel project.
2. Register `App\Providers\CustomersServiceProvider::class` in `bootstrap/providers.php` (Laravel 11/12).
3. Inside the existing authenticated `/api/v1` route group in `routes/api.php`, require:
   ```php
   require __DIR__.'/customers.php';
   ```
4. Assign Laravel Gate/permission abilities:
   - `customers.view`
   - `customers.create`
   - `customers.update`
   - `customers.groups.view`
   - `customers.groups.manage`
   - `customers.loyalty.view`
   - `customers.loyalty.manage`
5. Run:
   ```powershell
   php artisan migrate
   php artisan optimize:clear
   php artisan route:list --path=customers
   ```

## Frontend installation
1. Copy `frontend/resources/js/api/customers.ts` and `frontend/resources/js/modules/customers/` into the existing Vue `resources/js` tree.
2. Add the exports to your central registries:
   ```ts
   // frontend/resources/js/menu/index.ts
   import { customerMenu } from '@/modules/customers/menu'
   // Add customerMenu to the existing sidebarMenus array.
   ```
   ```ts
   // frontend/resources/js/router/moduleRoutes.ts
   import { customerRoutes } from '@/modules/customers/routes'
   // Add ...customerRoutes to the existing moduleRoutes array.
   ```
3. Run:
   ```powershell
   npm run build
   ```

## API
| Method | Endpoint | Ability |
|---|---|---|
| GET | `customers` | customers.view |
| POST | `customers` | customers.create |
| GET | `customers/{customer}` | customers.view |
| PUT | `customers/{customer}` | customers.update |
| GET | `customer-groups` | customers.groups.view |
| POST | `customer-groups` | customers.groups.manage |
| PUT | `customer-groups/{customerGroup}` | customers.groups.manage |
| GET | `customers/{customer}/loyalty` | customers.loyalty.view |
| POST | `customers/{customer}/loyalty` | customers.loyalty.manage |

## Loyalty request
Earn:
```json
{"type":"earn","points":25,"note":"Promotional reward"}
```
Redeem:
```json
{"type":"redeem","points":10,"note":"Redeemed at checkout"}
```
Adjustment:
```json
{"type":"adjustment","points":5,"direction":"subtract","note":"Correction"}
```
For `adjustment`, direction is `add` or `subtract`. All point changes are recorded in the ledger with the resulting balance. The service locks the customer row and rejects redemptions/adjustments that would create a negative balance.

## Important integration notes
- This package records points but does not automatically calculate points from sales. Integrate that only after checking the actual Step 4 sale completion service and transaction boundaries.
- Customer deletion is intentionally not exposed. Deactivate customers using `is_active=false` so customer and loyalty history are retained.
- Customer groups are labels only in this phase; group-specific earning multipliers are not applied.
- Review your existing permission seeder and assign the abilities to roles before using the screens.
