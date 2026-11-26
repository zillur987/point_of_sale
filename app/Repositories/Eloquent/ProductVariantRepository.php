<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ProductVariant;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/** @extends BaseRepository<ProductVariant> */
final class ProductVariantRepository extends BaseRepository implements ProductVariantRepositoryInterface
{
    public function __construct(ProductVariant $model)
    {
        parent::__construct($model);
    }

    public function forProduct(int $productId): Collection
    {
        return $this->query()->where('product_id', $productId)->get();
    }

    public function findByBarcode(string $barcode): ?ProductVariant
    {
        return $this->sellable()->where('barcode', $barcode)->first();
    }

    public function searchSellable(string $term, int $limit = 20): Collection
    {
        return $this->sellable()
            ->where(fn (Builder $q) => $q
                ->where('barcode', $term)
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
                ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', "%{$term}%")))
            ->limit($limit)
            ->get();
    }

    /** Active variants of active (non-deleted) products, with product eager-loaded. */
    private function sellable(): Builder
    {
        return $this->query()
            ->with('product.unit')
            ->where('is_active', true)
            ->whereHas('product', fn (Builder $p) => $p->where('is_active', true));
    }
}
