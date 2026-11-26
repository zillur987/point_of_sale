<?php

namespace App\Repositories\Eloquent;

use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentStockAdjustmentRepository implements StockAdjustmentRepositoryInterface
{
    private const DETAIL = ['warehouse:id,name,code', 'user:id,name', 'items.variant.product.unit'];

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return StockAdjustment::query()
            ->with(['warehouse:id,name,code', 'user:id,name'])
            ->withCount('items')
            ->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($filters['reason'] ?? null, fn ($q, $reason) => $q->where('reason', $reason))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('reference_no', 'like', "%{$s}%"))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('adjusted_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('adjusted_at', '<=', $d))
            ->orderByDesc('adjusted_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $id): ?StockAdjustment
    {
        return StockAdjustment::with(self::DETAIL)->find($id);
    }

    public function create(array $data, array $items): StockAdjustment
    {
        $adjustment = StockAdjustment::create($data);
        $adjustment->items()->createMany($items);
        $adjustment->update([
            'reference_no' => sprintf('%s-%06d', StockAdjustment::REFERENCE_PREFIX, $adjustment->id),
        ]);

        return $adjustment->load(self::DETAIL);
    }
}
