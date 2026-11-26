<?php

namespace App\Http\Requests\Inventory;

class StockTransferIndexRequest extends PaginatedIndexRequest
{
    protected function filterRules(): array
    {
        return [
            'from_warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'to_warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
        ];
    }
}
