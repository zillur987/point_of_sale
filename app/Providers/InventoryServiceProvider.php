<?php

namespace App\Providers;

use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use App\Repositories\Contracts\StockableVariantRepositoryInterface;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Eloquent\EloquentStockableVariantRepository;
use App\Repositories\Eloquent\EloquentStockAdjustmentRepository;
use App\Repositories\Eloquent\EloquentStockMovementRepository;
use App\Repositories\Eloquent\EloquentStockRepository;
use App\Repositories\Eloquent\EloquentStockTransferRepository;
use App\Repositories\Eloquent\EloquentWarehouseRepository;
use App\Services\Contracts\StockServiceInterface;
use App\Services\Inventory\StockService;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        WarehouseRepositoryInterface::class => EloquentWarehouseRepository::class,
        StockRepositoryInterface::class => EloquentStockRepository::class,
        StockMovementRepositoryInterface::class => EloquentStockMovementRepository::class,
        StockAdjustmentRepositoryInterface::class => EloquentStockAdjustmentRepository::class,
        StockTransferRepositoryInterface::class => EloquentStockTransferRepository::class,
        StockableVariantRepositoryInterface::class => EloquentStockableVariantRepository::class,
        StockServiceInterface::class => StockService::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/inventory.php', 'inventory');

        $this->app->when(StockService::class)
            ->needs('$allowNegative')
            ->giveConfig('inventory.allow_negative_stock');
    }
}
