<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ProductData;
use App\Exceptions\BusinessRuleException;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final readonly class ProductService
{
    private const RELATIONS = ['category', 'brand', 'unit', 'variants'];

    public function __construct(
        private ProductRepositoryInterface $products,
        private ProductVariantRepositoryInterface $variants,
        private BarcodeService $barcodes,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->products->paginate($perPage, $filters, self::RELATIONS);
    }

    public function show(Product $product): Product
    {
        return $product->load(self::RELATIONS);
    }

    public function create(ProductData $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            /** @var Product $product */
            $product = $this->products->create($data->productAttributes());

            $this->syncVariants($product, $data);

            return $this->products->findOrFail($product->id, self::RELATIONS);
        });
    }

    public function update(Product $product, ProductData $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $this->products->update($product, $data->productAttributes());

            $this->syncVariants($product, $data);

            return $this->products->findOrFail($product->id, self::RELATIONS);
        });
    }

    public function delete(Product $product): void
    {
        // Variants are soft-deleted with the product so historical stock/sales rows stay intact.
        DB::transaction(function () use ($product): void {
            $product->variants()->delete();
            $this->products->delete($product);
        });
    }

    /**
     * Upsert submitted variants and soft-delete the ones that were removed.
     * Variants are soft-deleted (never hard-deleted): stock and sales reference variant_id.
     */
    private function syncVariants(Product $product, ProductData $data): void
    {
        $existing = $this->variants->forProduct($product->id)->keyBy('id');
        $keptIds = [];

        foreach ($data->variants as $variantData) {
            if ($variantData->id !== null) {
                $variant = $existing->get($variantData->id)
                    ?? throw new BusinessRuleException("Variant #{$variantData->id} does not belong to this product.");

                $this->variants->update($variant, $variantData->toArray());
                $keptIds[] = $variant->id;

                continue;
            }

            $variant = $this->variants->create([...$variantData->toArray(), 'product_id' => $product->id]);

            if ($variant->barcode === null) {
                $this->variants->update($variant, ['barcode' => $this->barcodes->forVariant($variant->id)]);
            }

            $keptIds[] = $variant->id;
        }

        $existing->except($keptIds)->each(fn ($variant) => $this->variants->delete($variant));
    }
}
