<?php

namespace App\DataTransferObjects\Inventory;

use App\Enums\Inventory\MovementType;
use Illuminate\Database\Eloquent\Model;

/** One signed change to one stock level. Positive quantity = stock in, negative = stock out. */
final readonly class StockMovementData
{
    public function __construct(
        public int $warehouseId,
        public int $variantId,
        public string $quantity,
        public MovementType $type,
        public ?int $userId = null,
        public ?Model $reference = null,
        public ?string $note = null,
    ) {
    }
}
