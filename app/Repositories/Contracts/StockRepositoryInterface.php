<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockRepositoryInterface
{
    /**
     * @param array{warehouse_id?: int, product_variant_id?: int, search?: string, category_id?: int, brand_id?: int, low_stock?: bool, in_stock?: bool} $filters
     */
    public function paginateLevels(array $filters, int $perPage): LengthAwarePaginator;

    /** @return array{lines: int, total_quantity: string, stock_value: int, low_stock_count: int} stock_value is in minor units */
    public function summary(array $filters): array;

    /**
     * Atomically add a signed delta to a stock level (creating it at zero if missing).
     *
     * @return string|null the new balance, or null when the change was refused because it would go below zero
     */
    public function applyDelta(int $warehouseId, int $variantId, string $delta, bool $allowNegative): ?string;

    public function balance(int $warehouseId, int $variantId): string;
}
