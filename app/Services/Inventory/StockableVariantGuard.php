<?php

namespace App\Services\Inventory;

use App\Repositories\Contracts\StockableVariantRepositoryInterface;
use App\Support\Quantity;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Catalog rules that stock documents must respect: the product must track stock,
 * and units that do not allow decimals only accept whole quantities.
 * Errors are keyed "items.{i}.field" so the form shows them under the right row.
 */
final class StockableVariantGuard
{
    public function __construct(private readonly StockableVariantRepositoryInterface $variants)
    {
    }

    /**
     * @param list<array{product_variant_id: int, quantity: string}> $items
     * @return Collection<int, \App\Models\ProductVariant>
     *
     * @throws ValidationException
     */
    public function validate(array $items): Collection
    {
        $variants = $this->variants->findManyWithUnit(array_column($items, 'product_variant_id'));
        $errors = [];

        foreach ($items as $i => $item) {
            $product = $variants->get($item['product_variant_id'])?->product;

            if (! $product) {
                $errors["items.{$i}.product_variant_id"] = 'This product no longer exists.';
            } elseif (! $product->track_stock) {
                $errors["items.{$i}.product_variant_id"] = "{$product->name} does not track stock.";
            } elseif (! ($product->unit?->allow_decimal) && ! Quantity::isWhole($item['quantity'])) {
                $errors["items.{$i}.quantity"] = "{$product->name} can only be counted in whole units.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $variants;
    }
}
