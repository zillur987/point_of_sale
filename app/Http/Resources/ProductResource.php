<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
final class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'description' => $this->description,
            'track_stock' => $this->track_stock,
            'alert_quantity' => $this->alert_quantity,
            'tax_rate_bps' => $this->tax_rate_bps,
            'tax_inclusive' => $this->tax_inclusive,
            'is_active' => $this->is_active,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'unit_id' => $this->unit_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
