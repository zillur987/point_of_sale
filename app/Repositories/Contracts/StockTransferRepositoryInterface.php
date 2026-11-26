<?php

namespace App\Repositories\Contracts;

use App\Models\StockTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockTransferRepositoryInterface
{
    /** @param array{from_warehouse_id?: int, to_warehouse_id?: int, search?: string, date_from?: string, date_to?: string} $filters */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    public function find(int $id): ?StockTransfer;

    /** @param list<array{product_variant_id: int, quantity: string}> $items */
    public function create(array $data, array $items): StockTransfer;
}
