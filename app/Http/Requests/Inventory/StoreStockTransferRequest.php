<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware: can:inventory.manage
    }

    public function rules(): array
    {
        $activeWarehouse = Rule::exists('warehouses', 'id')->where('is_active', true);

        return [
            'from_warehouse_id' => ['required', 'integer', $activeWarehouse],
            'to_warehouse_id' => ['required', 'integer', 'different:from_warehouse_id', $activeWarehouse],
            'transferred_at' => ['nullable', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_variant_id' => ['required', 'integer', 'distinct', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_warehouse_id.different' => 'Source and destination warehouses must be different.',
            'items.*.product_variant_id.distinct' => 'This product is listed more than once.',
            'items.*.quantity.gt' => 'Quantity must be greater than zero.',
        ];
    }
}
