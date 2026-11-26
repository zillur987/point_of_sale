<?php

namespace App\Http\Requests\Inventory;

class WarehouseIndexRequest extends PaginatedIndexRequest
{
    protected function filterRules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
