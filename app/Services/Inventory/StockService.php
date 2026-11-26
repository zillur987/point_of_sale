<?php

namespace App\Services\Inventory;

use App\DataTransferObjects\Inventory\StockMovementData;
use App\Exceptions\Inventory\InsufficientStockException;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Repositories\Contracts\StockableVariantRepositoryInterface;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Services\Contracts\StockServiceInterface;
use App\Support\Quantity;
use Illuminate\Support\Facades\DB;

final class StockService implements StockServiceInterface
{
    public function __construct(
        private readonly StockRepositoryInterface $stock,
        private readonly StockMovementRepositoryInterface $movements,
        private readonly StockableVariantRepositoryInterface $variants,
        private readonly bool $allowNegative = false,
    ) {
    }

    public function move(StockMovementData $data): StockMovement
    {
        $quantity = Quantity::normalize($data->quantity);

        return DB::transaction(function () use ($data, $quantity) {
            $balance = $this->stock->applyDelta($data->warehouseId, $data->variantId, $quantity, $this->allowNegative);

            if ($balance === null) {
                throw InsufficientStockException::for(
                    $this->label($data->variantId),
                    $this->stock->balance($data->warehouseId, $data->variantId),
                    Quantity::negate($quantity),
                );
            }

            return $this->movements->create([
                'warehouse_id' => $data->warehouseId,
                'product_variant_id' => $data->variantId,
                'type' => $data->type,
                'quantity' => $quantity,
                'balance_after' => $balance,
                'reference_type' => $data->reference?->getMorphClass(),
                'reference_id' => $data->reference?->getKey(),
                'note' => $data->note,
                'user_id' => $data->userId,
            ]);
        });
    }

    public function available(int $warehouseId, int $variantId): string
    {
        return $this->stock->balance($warehouseId, $variantId);
    }

    private function label(int $variantId): string
    {
        /** @var ProductVariant|null $variant */
        $variant = $this->variants->findManyWithUnit([$variantId])->first();

        if (! $variant) {
            return "variant #{$variantId}";
        }

        $name = $variant->product?->name ?? 'Product';
        $suffix = ($variant->name && $variant->name !== 'Default') ? " - {$variant->name}" : '';

        return "{$name}{$suffix} ({$variant->sku})";
    }
}
