<?php

namespace App\Repositories\Contracts;

use App\Models\StockAdjustment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockAdjustmentRepositoryInterface
{
    /** @param array{warehouse_id?: int, reason?: string, search?: string, date_from?: string, date_to?: string} $filters */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function find(int $id): ?StockAdjustment;

    /** @param list<array{product_variant_id: int, direction: string, quantity: string}> $items */
    public function create(array $data, array $items): StockAdjustment;
}
