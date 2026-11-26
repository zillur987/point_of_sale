<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\{SaleRepositoryInterface,PosCatalogRepositoryInterface};
use App\Repositories\Eloquent\{EloquentSaleRepository,EloquentPosCatalogRepository};
use App\Services\Contracts\SaleServiceInterface;
use App\Services\Sales\SaleService;
class SalesServiceProvider extends ServiceProvider {
 public function register(): void {
  $this->app->bind(SaleRepositoryInterface::class,EloquentSaleRepository::class);
  $this->app->bind(PosCatalogRepositoryInterface::class,EloquentPosCatalogRepository::class);
  $this->app->bind(SaleServiceInterface::class,SaleService::class);
 }
}
