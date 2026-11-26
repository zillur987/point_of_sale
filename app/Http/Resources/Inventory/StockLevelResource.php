<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\StockLevel */
class StockLevelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->variant?->product;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'is_low' => $product
                && $product->track_stock
                && (float) $this->quantity <= (float) $product->alert_quantity,
            'warehouse' => $this->whenLoaded('warehouse', fn () => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
                'code' => $this->warehouse->code,
            ]),
            'variant' => new StockVariantResource($this->whenLoaded('variant')),
        ];
    }
}
