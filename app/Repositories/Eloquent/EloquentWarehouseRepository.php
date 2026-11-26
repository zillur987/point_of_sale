<?php

namespace App\Repositories\Eloquent;

use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return Warehouse::query()
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")
            ))
            ->when(array_key_exists('is_active', $filters), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function options(): Collection
    {
        return Warehouse::query()->where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
    }

    public function find(int $id): ?Warehouse
    {
        return Warehouse::find($id);
    }

    public function findDefault(): ?Warehouse
    {
        return Warehouse::query()->where('is_default', true)->first();
    }

    public function count(): int
    {
        return Warehouse::count();
    }

    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        return $warehouse;
    }

    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }

    public function unsetDefault(?int $exceptId = null): void
    {
        Warehouse::query()
            ->where('is_default', true)
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }

    public function hasStockHistory(Warehouse $warehouse): bool
    {
        return StockMovement::query()->where('warehouse_id', $warehouse->id)->exists();
    }
}
