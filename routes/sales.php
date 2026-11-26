<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Sales\PosController;
Route::middleware('can:sales.view')->group(function(){
 Route::get('sales',[PosController::class,'index']);
 Route::get('sales/{sale}',[PosController::class,'show'])->whereNumber('sale');
});
Route::middleware('can:sales.create')->group(function(){
 Route::get('pos/catalog',[PosController::class,'catalog']);
 Route::post('pos/checkout',[PosController::class,'checkout']);
});
