<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLoyaltyTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'type',
        'points_change',
        'balance_after',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'points_change' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
