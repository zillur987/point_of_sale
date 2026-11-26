<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

/**
 * Read-only view of catalog variants for the inventory module. Keeps inventory decoupled
 * from however the catalog module loads its own data.
 */
interface StockableVariantRepositoryInterface
{
    /** @param list<int> $ids @return Collection<int, \App\Models\ProductVariant> keyed by id, with product.unit loaded */
    public function findManyWithUnit(array $ids): Collection;
}
