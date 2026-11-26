<?php

use App\Http\Controllers\Api\V1\Inventory\StockAdjustmentController;
use App\Http\Controllers\Api\V1\Inventory\StockController;
use App\Http\Controllers\Api\V1\Inventory\StockMovementController;
use App\Http\Controllers\Api\V1\Inventory\StockTransferController;
use App\Http\Controllers\Api\V1\Inventory\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
| Loaded from routes/api.php INSIDE your existing authenticated `v1` group:
|
|     Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
|         ...
|         require __DIR__.'/inventory.php';
|     });
*/

Route::middleware('can:inventory.view')->group(function () {
    Route::get('warehouses/options', [WarehouseController::class, 'options']); // before {warehouse}
    Route::get('warehouses', [WarehouseController::class, 'index']);
    Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->whereNumber('warehouse');

    Route::get('stock', [StockController::class, 'index']);
    Route::get('stock/summary', [StockController::class, 'summary']);
    Route::get('stock/movements', [StockMovementController::class, 'index']);

    Route::get('stock/adjustments', [StockAdjustmentController::class, 'index']);
    Route::get('stock/adjustments/{id}', [StockAdjustmentController::class, 'show'])->whereNumber('id');

    Route::get('stock/transfers', [StockTransferController::class, 'index']);
    Route::get('stock/transfers/{id}', [StockTransferController::class, 'show'])->whereNumber('id');
});

Route::middleware('can:inventory.manage')->group(function () {
    Route::post('warehouses', [WarehouseController::class, 'store']);
    Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->whereNumber('warehouse');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->whereNumber('warehouse');

    Route::post('stock/adjustments', [StockAdjustmentController::class, 'store']);
    Route::post('stock/transfers', [StockTransferController::class, 'store']);
});
