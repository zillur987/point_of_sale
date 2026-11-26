<?php

namespace App\Repositories\Contracts;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface
{
    public function create(array $data): StockMovement;

    /**
     * @param array{warehouse_id?: int, product_variant_id?: int, type?: string, date_from?: string, date_to?: string, search?: string} $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;
}
