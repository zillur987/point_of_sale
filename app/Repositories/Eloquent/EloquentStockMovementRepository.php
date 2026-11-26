<?php

namespace App\Repositories\Eloquent;

use App\Models\StockMovement;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with(['warehouse:id,name,code', 'variant.product:id,name,unit_id', 'user:id,name'])
            ->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($filters['product_variant_id'] ?? null, fn ($q, $id) => $q->where('product_variant_id', $id))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', "{$d} 00:00:00"))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('created_at', '<=', "{$d} 23:59:59"))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('variant', fn ($v) => $v->where(
                fn ($w) => $w->where('sku', 'like', "%{$s}%")
                    ->orWhere('barcode', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$s}%"))
            )))
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
