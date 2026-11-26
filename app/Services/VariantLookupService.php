<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductVariant;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/** Read-side service used by the POS screen (barcode scan and product search). */
final readonly class VariantLookupService
{
    public function __construct(private ProductVariantRepositoryInterface $variants) {}

    public function byBarcode(string $barcode): ?ProductVariant
    {
        return $this->variants->findByBarcode($barcode);
    }

    /** @return Collection<int, ProductVariant> */
    public function search(string $term, int $limit = 20): Collection
    {
        return $this->variants->searchSellable($term, $limit);
    }
}
