<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'category_id', 'brand_id', 'unit_id', 'description',
        'track_stock', 'alert_quantity', 'tax_rate_bps', 'tax_inclusive', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'track_stock' => 'boolean',
            'tax_inclusive' => 'boolean',
            'is_active' => 'boolean',
            'alert_quantity' => 'decimal:3',
            'tax_rate_bps' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('id');
    }
}
