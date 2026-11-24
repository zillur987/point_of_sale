# POS System: Step 1 (Foundation + Branches)

Copy the folders into your Laravel project root (they mirror Laravel's structure).

1. Copy `app/`, `database/`, `tests/`, `resources/js/` into your project.
2. Register `App\Providers\RepositoryServiceProvider` in `bootstrap/providers.php` (see `bootstrap_providers_snippet.php`).
3. Paste the routes from `routes/api_pos_snippet.php` into `routes/api.php` and add your existing permission middleware.
4. `php artisan migrate`
5. `php artisan test --filter=BranchApiTest`

Requirements: PHP 8.3+, Laravel 11/12, Sanctum. Frontend files assume an Axios instance at `@/api/http`, Pinia and TypeScript.
Delete the two `*_snippet.php` files after copying.

Conventions: money as integer minor units, `branch_id` on every transactional table, soft deletes on master data, append-only ledgers.
