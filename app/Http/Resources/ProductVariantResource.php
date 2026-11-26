<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ProductVariant */
final class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'attributes' => $this->attributes,
            'is_active' => $this->is_active,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
