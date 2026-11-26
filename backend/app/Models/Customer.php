<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_group_id',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'points_balance' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(CustomerLoyaltyTransaction::class);
    }
}
