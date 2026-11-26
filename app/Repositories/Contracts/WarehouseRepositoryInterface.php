<?php

namespace App\Repositories\Contracts;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WarehouseRepositoryInterface
{
    /** @param array{search?: string, is_active?: bool} $filters */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    /** Active warehouses for dropdowns. */
    public function options(): Collection;

    public function find(int $id): ?Warehouse;

    public function findDefault(): ?Warehouse;

    public function count(): int;

    public function create(array $data): Warehouse;

    public function update(Warehouse $warehouse, array $data): Warehouse;

    public function delete(Warehouse $warehouse): void;

    public function unsetDefault(?int $exceptId = null): void;

    public function hasStockHistory(Warehouse $warehouse): bool;
}
