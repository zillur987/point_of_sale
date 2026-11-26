<?php

namespace App\Http\Requests\Inventory;

class StockIndexRequest extends PaginatedIndexRequest
{
    protected function filterRules(): array
    {
        return [
            'warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'product_variant_id' => ['sometimes', 'integer'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'category_id' => ['sometimes', 'integer'],
            'brand_id' => ['sometimes', 'integer'],
            'low_stock' => ['sometimes', 'boolean'],
            'in_stock' => ['sometimes', 'boolean'],
        ];
    }
}
