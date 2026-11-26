<?php

namespace App\Models;

use App\Enums\Inventory\MovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** Append-only ledger row. Never update or delete these. */
class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'warehouse_id', 'product_variant_id', 'type', 'quantity', 'balance_after',
        'reference_type', 'reference_id', 'note', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'quantity' => 'decimal:3',
            'balance_after' => 'decimal:3',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
