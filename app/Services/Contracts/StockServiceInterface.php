<?php

namespace App\Services\Contracts;

use App\DataTransferObjects\Inventory\StockMovementData;
use App\Models\StockMovement;

/**
 * The single entry point for changing stock. Adjustments, transfers and (next step) sales,
 * purchases and returns all go through it, so the ledger can never drift from the balances.
 */
interface StockServiceInterface
{
    /**
     * Apply one signed movement: update the balance and append the ledger row, atomically.
     *
     * @throws \App\Exceptions\Inventory\InsufficientStockException
     */
    public function move(StockMovementData $data): StockMovement;

    public function available(int $warehouseId, int $variantId): string;
}
