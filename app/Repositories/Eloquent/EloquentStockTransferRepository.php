<?php

namespace App\Repositories\Eloquent;

use App\Models\StockTransfer;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentStockTransferRepository implements StockTransferRepositoryInterface
{
    private const DETAIL = ['fromWarehouse:id,name,code', 'toWarehouse:id,name,code', 'user:id,name', 'items.variant.product.unit'];

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return StockTransfer::query()
            ->with(['fromWarehouse:id,name,code', 'toWarehouse:id,name,code', 'user:id,name'])
            ->withCount('items')
            ->when($filters['from_warehouse_id'] ?? null, fn ($q, $id) => $q->where('from_warehouse_id', $id))
            ->when($filters['to_warehouse_id'] ?? null, fn ($q, $id) => $q->where('to_warehouse_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('reference_no', 'like', "%{$s}%"))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('transferred_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('transferred_at', '<=', $d))
            ->orderByDesc('transferred_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $id): ?StockTransfer
    {
        return StockTransfer::with(self::DETAIL)->find($id);
    }

    public function create(array $data, array $items): StockTransfer
    {
        $transfer = StockTransfer::create($data);
        $transfer->items()->createMany($items);
        $transfer->update([
            'reference_no' => sprintf('%s-%06d', StockTransfer::REFERENCE_PREFIX, $transfer->id),
        ]);

        return $transfer->load(self::DETAIL);
    }
}
