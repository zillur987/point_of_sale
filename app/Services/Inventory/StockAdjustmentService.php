<?php

namespace App\Services\Inventory;

use App\DataTransferObjects\Inventory\StockMovementData;
use App\Enums\Inventory\AdjustmentDirection;
use App\Enums\Inventory\AdjustmentReason;
use App\Enums\Inventory\MovementType;
use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use App\Services\Contracts\StockServiceInterface;
use App\Support\Quantity;
use Illuminate\Support\Facades\DB;

final class StockAdjustmentService
{
    public function __construct(
        private readonly StockAdjustmentRepositoryInterface $adjustments,
        private readonly StockServiceInterface $stock,
        private readonly StockableVariantGuard $guard,
    ) {
    }

    /**
     * @param array{warehouse_id: int, reason: string, note?: ?string, adjusted_at?: ?string,
     *              items: list<array{product_variant_id: int, direction: string, quantity: string|float|int}>} $data
     */
    public function create(array $data, ?int $userId): StockAdjustment
    {
        $reason = AdjustmentReason::from($data['reason']);

        $items = array_map(fn (array $item) => [
            'product_variant_id' => (int) $item['product_variant_id'],
            'direction' => $item['direction'],
            'quantity' => Quantity::normalize($item['quantity']),
        ], array_values($data['items']));

        $this->guard->validate($items);

        return DB::transaction(function () use ($data, $reason, $items, $userId) {
            $adjustment = $this->adjustments->create([
                'warehouse_id' => $data['warehouse_id'],
                'reason' => $reason,
                'note' => $data['note'] ?? null,
                'adjusted_at' => $data['adjusted_at'] ?? now()->toDateString(),
                'user_id' => $userId,
            ], $items);

            // Apply in a stable order so concurrent documents touch rows in the same sequence.
            usort($items, fn ($a, $b) => $a['product_variant_id'] <=> $b['product_variant_id']);

            foreach ($items as $item) {
                $direction = AdjustmentDirection::from($item['direction']);

                $this->stock->move(new StockMovementData(
                    warehouseId: $adjustment->warehouse_id,
                    variantId: $item['product_variant_id'],
                    quantity: $direction->isInbound() ? $item['quantity'] : Quantity::negate($item['quantity']),
                    type: $this->movementType($reason, $direction),
                    userId: $userId,
                    reference: $adjustment,
                    note: $adjustment->reference_no.' - '.($data['note'] ?? $reason->label()),
                ));
            }

            return $adjustment;
        });
    }

    private function movementType(AdjustmentReason $reason, AdjustmentDirection $direction): MovementType
    {
        if ($reason === AdjustmentReason::OpeningStock) {
            return MovementType::Opening;
        }

        return $direction->isInbound() ? MovementType::AdjustmentIn : MovementType::AdjustmentOut;
    }
}
