<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\{PurchaseCatalogRepositoryInterface,PurchaseRepositoryInterface,SupplierRepositoryInterface};
use App\Repositories\Eloquent\{EloquentPurchaseCatalogRepository,EloquentPurchaseRepository,EloquentSupplierRepository};
use App\Services\Contracts\PurchaseServiceInterface;
use App\Services\Purchases\PurchaseService;
class PurchasesServiceProvider extends ServiceProvider
{
 public array $bindings=[
  SupplierRepositoryInterface::class=>EloquentSupplierRepository::class,
  PurchaseRepositoryInterface::class=>EloquentPurchaseRepository::class,
  PurchaseCatalogRepositoryInterface::class=>EloquentPurchaseCatalogRepository::class,
  PurchaseServiceInterface::class=>PurchaseService::class,
 ];
}
