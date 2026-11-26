<?php

namespace App\Http\Requests\Inventory;

use App\Enums\Inventory\MovementType;
use Illuminate\Validation\Rule;

class StockMovementIndexRequest extends PaginatedIndexRequest
{
    protected function filterRules(): array
    {
        return [
            'warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'product_variant_id' => ['sometimes', 'integer'],
            'type' => ['sometimes', Rule::enum(MovementType::class)],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
        ];
    }
}
