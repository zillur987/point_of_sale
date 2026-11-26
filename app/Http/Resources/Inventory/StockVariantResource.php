<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Compact variant + product + unit view used by every inventory screen. @mixin \App\Models\ProductVariant */
class StockVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'track_stock' => (bool) $product->track_stock,
                'alert_quantity' => (string) $product->alert_quantity,
                'unit' => $product->unit ? [
                    'id' => $product->unit->id,
                    'name' => $product->unit->name,
                    'short_name' => $product->unit->short_name,
                    'allow_decimal' => (bool) $product->unit->allow_decimal,
                ] : null,
            ] : null,
        ];
    }
}
