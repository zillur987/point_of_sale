<?php

namespace App\Services\Inventory;

use App\DataTransferObjects\Inventory\StockMovementData;
use App\Enums\Inventory\MovementType;
use App\Models\StockTransfer;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use App\Services\Contracts\StockServiceInterface;
use App\Support\Quantity;
use Illuminate\Support\Facades\DB;

final class StockTransferService
{
    public function __construct(
        private readonly StockTransferRepositoryInterface $transfers,
        private readonly StockServiceInterface $stock,
        private readonly StockableVariantGuard $guard,
    ) {
    }

    /**
     * Moves stock immediately: out of the source warehouse, into the destination, one transaction.
     *
     * @param array{from_warehouse_id: int, to_warehouse_id: int, note?: ?string, transferred_at?: ?string,
     *              items: list<array{product_variant_id: int, quantity: string|float|int}>} $data
     */
    public function create(array $data, ?int $userId): StockTransfer
    {
        $items = array_map(fn (array $item) => [
            'product_variant_id' => (int) $item['product_variant_id'],
            'quantity' => Quantity::normalize($item['quantity']),
        ], array_values($data['items']));

        $this->guard->validate($items);

        return DB::transaction(function () use ($data, $items, $userId) {
            $transfer = $this->transfers->create([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'note' => $data['note'] ?? null,
                'transferred_at' => $data['transferred_at'] ?? now()->toDateString(),
                'user_id' => $userId,
            ], $items);

            $note = $transfer->reference_no.(! empty($data['note']) ? ' - '.$data['note'] : '');
            $movements = [];

            foreach ($items as $item) {
                $movements[] = new StockMovementData(
                    $transfer->from_warehouse_id, $item['product_variant_id'], Quantity::negate($item['quantity']),
                    MovementType::TransferOut, $userId, $transfer, $note,
                );
                $movements[] = new StockMovementData(
                    $transfer->to_warehouse_id, $item['product_variant_id'], $item['quantity'],
                    MovementType::TransferIn, $userId, $transfer, $note,
                );
            }

            // One global order (warehouse, then variant). Two opposite transfers (A->B and B->A)
            // therefore lock rows in the same sequence and can never deadlock each other.
            usort($movements, fn (StockMovementData $a, StockMovementData $b) => [$a->warehouseId, $a->variantId] <=> [$b->warehouseId, $b->variantId]);

            foreach ($movements as $movement) {
                $this->stock->move($movement);
            }

            return $transfer;
        });
    }
}
