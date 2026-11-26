<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;

/** @extends BaseRepositoryInterface<ProductVariant> */
interface ProductVariantRepositoryInterface extends BaseRepositoryInterface
{
    /** @return Collection<int, ProductVariant> */
    public function forProduct(int $productId): Collection;

    public function findByBarcode(string $barcode): ?ProductVariant;

    /** @return Collection<int, ProductVariant> */
    public function searchSellable(string $term, int $limit = 20): Collection;
}
