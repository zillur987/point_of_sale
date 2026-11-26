<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InventoryAdController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\MyAccessController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UserAccessController;
use App\Http\Controllers\Api\V1\VariantLookupController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        $resource = function (string $uri, string $controller, string $module, string $alsoView = ''): void {
            $view = "permission:{$module}.view" . ($alsoView !== '' ? ",{$alsoView}" : '');

            Route::apiResource($uri, $controller)->only(['index', 'show'])->middleware($view);
            Route::apiResource($uri, $controller)->only(['store'])->middleware("permission:{$module}.create");
            Route::apiResource($uri, $controller)->only(['update'])->middleware("permission:{$module}.edit");
            Route::apiResource($uri, $controller)->only(['destroy'])->middleware("permission:{$module}.delete");
        };

        Route::get('dashboard', DashboardController::class)->middleware('permission:dashboard.view');

        Route::get('auth/permissions', [MyAccessController::class, 'show'])->name('auth.permissions');

        Route::get('roles/summary', [RoleController::class, 'summary'])->middleware('permission:roles.view')->name('roles.summary');
        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('roles.index');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('roles.store');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view')->name('roles.show');
        Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.edit')->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('roles.destroy');

        Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:roles.view,roles.create,roles.edit,users.view,users.edit')->name('permissions.index');

        Route::get('access/users/summary', [UserAccessController::class, 'summary'])->middleware('permission:users.view')->name('access.users.summary');
        Route::get('access/users/lookups', [UserAccessController::class, 'lookups'])->middleware('permission:users.view')->name('access.users.lookups');
        Route::get('access/users', [UserAccessController::class, 'index'])->middleware('permission:users.view')->name('access.users.index');
        Route::get('access/users/{user}', [UserAccessController::class, 'show'])->middleware('permission:users.view')->name('access.users.show');
        Route::post('access/users', [UserAccessController::class, 'store'])->middleware('permission:users.create')->name('access.users.store');
        Route::put('access/users/{user}/roles', [UserAccessController::class, 'updateRoles'])->middleware('permission:users.edit')->name('access.users.roles');

        Route::apiResource('branches', BranchController::class);

        // Must be declared before apiResource so "tree" is not treated as a {category} id.
        Route::get('categories/tree', [CategoryController::class, 'tree']);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('brands', BrandController::class);
        Route::apiResource('units', UnitController::class);
        Route::apiResource('products', ProductController::class);

        // POS lookups
        Route::get('variants/search', [VariantLookupController::class, 'search']);
        Route::get('variants/barcode/{barcode}', [VariantLookupController::class, 'barcode']);
    });
});
