<?php

namespace App\Services\Inventory;

use App\Exceptions\Inventory\WarehouseRuleException;
use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class WarehouseService
{
    public function __construct(private readonly WarehouseRepositoryInterface $warehouses)
    {
    }

    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            // The very first warehouse is always the default so the POS always has somewhere to sell from.
            $isDefault = (bool) ($data['is_default'] ?? false) || $this->warehouses->count() === 0;

            if ($isDefault) {
                $this->warehouses->unsetDefault();
            }

            return $this->warehouses->create([
                ...$data,
                'is_default' => $isDefault,
                'is_active' => $isDefault ? true : (bool) ($data['is_active'] ?? true),
            ]);
        });
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data) {
            if ($warehouse->is_default && array_key_exists('is_active', $data) && ! $data['is_active']) {
                throw WarehouseRuleException::cannotDeactivateDefault();
            }

            // A default cannot be un-defaulted directly: promote another warehouse instead.
            $isDefault = $warehouse->is_default || (bool) ($data['is_default'] ?? false);

            if ($isDefault && ! $warehouse->is_default) {
                $this->warehouses->unsetDefault($warehouse->id);
            }

            return $this->warehouses->update($warehouse, [
                ...$data,
                'is_default' => $isDefault,
                'is_active' => $isDefault ? true : ($data['is_active'] ?? $warehouse->is_active),
            ]);
        });
    }

    public function delete(Warehouse $warehouse): void
    {
        if ($warehouse->is_default) {
            throw WarehouseRuleException::cannotDeleteDefault();
        }

        if ($this->warehouses->hasStockHistory($warehouse)) {
            throw WarehouseRuleException::hasStockHistory();
        }

        $this->warehouses->delete($warehouse);
    }
}
