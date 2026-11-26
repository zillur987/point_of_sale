<?php

use App\Http\Controllers\Api\V1\Customers\CustomerController;
use App\Http\Controllers\Api\V1\Customers\CustomerGroupController;
use App\Http\Controllers\Api\V1\Customers\CustomerLoyaltyController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:customers.view')->group(function () {
    Route::get('customers', [CustomerController::class, 'index']);
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->whereNumber('customer');
});

Route::middleware('can:customers.create')->post('customers', [CustomerController::class, 'store']);

Route::middleware('can:customers.update')->put('customers/{customer}', [CustomerController::class, 'update'])->whereNumber('customer');

Route::middleware('can:customers.groups.view')->get('customer-groups', [CustomerGroupController::class, 'index']);

Route::middleware('can:customers.groups.manage')->group(function () {
    Route::post('customer-groups', [CustomerGroupController::class, 'store']);
    Route::put('customer-groups/{customerGroup}', [CustomerGroupController::class, 'update'])->whereNumber('customerGroup');
});

Route::middleware('can:customers.loyalty.view')->get(
    'customers/{customer}/loyalty',
    [CustomerLoyaltyController::class, 'index']
)->whereNumber('customer');

Route::middleware('can:customers.loyalty.manage')->post(
    'customers/{customer}/loyalty',
    [CustomerLoyaltyController::class, 'store']
)->whereNumber('customer');
