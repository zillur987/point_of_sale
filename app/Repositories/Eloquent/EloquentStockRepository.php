<?php

namespace App\Repositories\Eloquent;

use App\Models\StockLevel;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Support\Quantity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentStockRepository implements StockRepositoryInterface
{
    public function paginateLevels(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->filtered($filters)
            ->with(['warehouse:id,name,code', 'variant.product.unit'])
            ->orderBy('stock_levels.product_variant_id')
            ->orderBy('stock_levels.warehouse_id')
            ->paginate($perPage);
    }

    public function summary(array $filters): array
    {
        $totals = $this->filtered($filters)
            ->join('product_variants as pv', 'pv.id', '=', 'stock_levels.product_variant_id')
            ->toBase()
            ->selectRaw('COUNT(*) as lines')
            ->selectRaw('COALESCE(SUM(stock_levels.quantity), 0) as total_quantity')
            ->selectRaw('COALESCE(SUM(stock_levels.quantity * pv.cost_price), 0) as stock_value')
            ->first();

        return [
            'lines' => (int) $totals->lines,
            'total_quantity' => Quantity::normalize($totals->total_quantity),
            'stock_value' => (int) round((float) $totals->stock_value),
            'low_stock_count' => $this->filtered([...$filters, 'low_stock' => true])->count(),
        ];
    }

    public function applyDelta(int $warehouseId, int $variantId, string $delta, bool $allowNegative): ?string
    {
        // Make sure the row exists, then change it with a single guarded UPDATE.
        // The guard makes "check + change" atomic, so two concurrent sales can never both
        // take the last unit - no read-modify-write race in PHP.
        StockLevel::query()->insertOrIgnore([[
            'warehouse_id' => $warehouseId,
            'product_variant_id' => $variantId,
            'quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]]);

        if ((float) $delta === 0.0) {
            return $this->balance($warehouseId, $variantId);
        }

        $query = StockLevel::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_variant_id', $variantId);

        if (! $allowNegative && (float) $delta < 0) {
            $query->whereRaw('quantity + ? >= 0', [$delta]);
        }

        $affected = $query->increment('quantity', Quantity::normalize($delta));

        return $affected > 0 ? $this->balance($warehouseId, $variantId) : null;
    }

    public function balance(int $warehouseId, int $variantId): string
    {
        $value = StockLevel::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_variant_id', $variantId)
            ->value('quantity');

        return Quantity::normalize($value ?? 0);
    }

    private function filtered(array $filters): Builder
    {
        return StockLevel::query()
            // Only stock-tracked products that still exist belong in inventory screens.
            ->whereHas('variant.product', fn ($p) => $p->where('track_stock', true))
            ->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('stock_levels.warehouse_id', $id))
            ->when($filters['product_variant_id'] ?? null, fn ($q, $id) => $q->where('stock_levels.product_variant_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('variant', fn ($v) => $v->where(
                fn ($w) => $w->where('sku', 'like', "%{$s}%")
                    ->orWhere('barcode', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$s}%"))
            )))
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->whereHas('variant.product', fn ($p) => $p->where('category_id', $id)))
            ->when($filters['brand_id'] ?? null, fn ($q, $id) => $q->whereHas('variant.product', fn ($p) => $p->where('brand_id', $id)))
            ->when(! empty($filters['low_stock']), fn ($q) => $q->whereHas(
                'variant.product',
                fn ($p) => $p->whereColumn('stock_levels.quantity', '<=', 'products.alert_quantity')
            ))
            ->when(! empty($filters['in_stock']), fn ($q) => $q->where('stock_levels.quantity', '>', 0));
    }
}
