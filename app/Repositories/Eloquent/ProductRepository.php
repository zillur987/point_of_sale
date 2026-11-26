<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/** @extends BaseRepository<Product> */
final class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $s) => $q->where(
                fn (Builder $w) => $w
                    ->where('name', 'like', "%{$s}%")
                    ->orWhereHas('variants', fn (Builder $v) => $v
                        ->where('sku', 'like', "%{$s}%")
                        ->orWhere('barcode', $s))
            ))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $id) => $q->where('category_id', $id))
            ->when($filters['brand_id'] ?? null, fn (Builder $q, $id) => $q->where('brand_id', $id))
            ->when($filters['type'] ?? null, fn (Builder $q, $type) => $q->where('type', $type))
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOL))
            );
    }
}
