<?php

namespace App\Models;

use App\Enums\Inventory\AdjustmentDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['stock_adjustment_id', 'product_variant_id', 'direction', 'quantity'];

    protected function casts(): array
    {
        return [
            'direction' => AdjustmentDirection::class,
            'quantity' => 'decimal:3',
        ];
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
