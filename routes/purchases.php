<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Purchases\{PurchaseController,SupplierController};
Route::middleware('can:suppliers.view')->group(function(){Route::get('suppliers',[SupplierController::class,'index']);Route::get('suppliers/{supplier}',[SupplierController::class,'show'])->whereNumber('supplier');});
Route::middleware('can:suppliers.create')->post('suppliers',[SupplierController::class,'store']);
Route::middleware('can:suppliers.update')->put('suppliers/{supplier}',[SupplierController::class,'update'])->whereNumber('supplier');
Route::middleware('can:purchases.view')->group(function(){Route::get('purchases',[PurchaseController::class,'index']);Route::get('purchases/{purchase}',[PurchaseController::class,'show'])->whereNumber('purchase');});
Route::middleware('can:purchases.create')->group(function(){Route::get('purchases/catalog',[PurchaseController::class,'catalog']);Route::post('purchases',[PurchaseController::class,'store']);});
Route::middleware('can:purchases.receive')->post('purchases/{purchase}/receive',[PurchaseController::class,'receive'])->whereNumber('purchase');
