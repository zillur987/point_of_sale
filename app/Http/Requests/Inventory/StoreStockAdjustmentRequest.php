<?php

namespace App\Http\Requests\Inventory;

use App\Enums\Inventory\AdjustmentDirection;
use App\Enums\Inventory\AdjustmentReason;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware: can:inventory.manage
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('is_active', true)],
            'reason' => ['required', Rule::enum(AdjustmentReason::class)],
            'adjusted_at' => ['nullable', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_variant_id' => ['required', 'integer', 'distinct', 'exists:product_variants,id'],
            'items.*.direction' => ['required', Rule::enum(AdjustmentDirection::class)],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('reason') !== AdjustmentReason::OpeningStock->value) {
                return;
            }

            foreach ((array) $this->input('items', []) as $i => $item) {
                if (($item['direction'] ?? null) === AdjustmentDirection::Out->value) {
                    $validator->errors()->add("items.{$i}.direction", 'Opening stock can only add stock.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.*.product_variant_id.distinct' => 'This product is listed more than once.',
            'items.*.quantity.gt' => 'Quantity must be greater than zero.',
        ];
    }
}
