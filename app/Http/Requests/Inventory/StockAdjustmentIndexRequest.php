<?php

namespace App\Http\Requests\Inventory;

use App\Enums\Inventory\AdjustmentReason;
use Illuminate\Validation\Rule;

class StockAdjustmentIndexRequest extends PaginatedIndexRequest
{
    protected function filterRules(): array
    {
        return [
            'warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'reason' => ['sometimes', Rule::enum(AdjustmentReason::class)],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
        ];
    }
}
