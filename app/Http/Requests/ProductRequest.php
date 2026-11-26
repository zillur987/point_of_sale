<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\ProductData;
use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ProductType::class)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')->whereNull('deleted_at')],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')->whereNull('deleted_at')],
            'description' => ['nullable', 'string', 'max:5000'],
            'track_stock' => ['sometimes', 'boolean'],
            'alert_quantity' => ['nullable', 'numeric', 'min:0'],
            'tax_rate_bps' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'tax_inclusive' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],

            // A single product has exactly one default variant; a variable product has one or more.
            'variants' => ['required', 'array', 'min:1', $this->input('type') === ProductType::Single->value ? 'max:1' : 'max:200'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.sku' => ['required', 'string', 'max:64', 'distinct:ignore_case'],
            'variants.*.barcode' => ['nullable', 'string', 'max:64', 'distinct'],
            'variants.*.cost_price' => ['required', 'integer', 'min:0'],
            'variants.*.sale_price' => ['required', 'integer', 'min:0'],
            'variants.*.attributes' => ['nullable', 'array'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
        ];

        // SKU and barcode must be unique across all variants (soft-deleted included, matching the DB index),
        // ignoring the variant being updated.
        $variants = $this->input('variants');

        foreach (is_array($variants) ? array_keys($variants) : [] as $i) {
            $ignoreId = $this->input("variants.{$i}.id");

            $rules["variants.{$i}.sku"][] = Rule::unique('product_variants', 'sku')->ignore($ignoreId);
            $rules["variants.{$i}.barcode"][] = Rule::unique('product_variants', 'barcode')->ignore($ignoreId);
        }

        return $rules;
    }

    public function toDto(): ProductData
    {
        return ProductData::fromArray($this->validated());
    }
}
