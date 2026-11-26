<?php

namespace App\Models;

use App\Enums\Inventory\AdjustmentReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockAdjustment extends Model
{
    public const REFERENCE_PREFIX = 'ADJ';

    protected $fillable = ['reference_no', 'warehouse_id', 'reason', 'note', 'adjusted_at', 'user_id'];

    protected function casts(): array
    {
        return [
            'reason' => AdjustmentReason::class,
            'adjusted_at' => 'date:Y-m-d',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
