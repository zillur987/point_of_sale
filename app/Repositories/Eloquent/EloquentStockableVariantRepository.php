<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductVariant;
use App\Repositories\Contracts\StockableVariantRepositoryInterface;
use Illuminate\Support\Collection;

final class EloquentStockableVariantRepository implements StockableVariantRepositoryInterface
{
    public function findManyWithUnit(array $ids): Collection
    {
        return ProductVariant::query()
            ->with('product.unit')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }
}
