<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\StockAdjustment */
class StockAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'reason' => $this->reason->value,
            'reason_label' => $this->reason->label(),
            'note' => $this->note,
            'adjusted_at' => $this->adjusted_at?->toDateString(),
            'items_count' => $this->whenCounted('items'),
            'warehouse' => $this->whenLoaded('warehouse', fn () => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
                'code' => $this->warehouse->code,
            ]),
            'user' => $this->whenLoaded('user', fn () => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'direction' => $item->direction->value,
                'quantity' => $item->quantity,
                'variant' => new StockVariantResource($item->variant),
            ])),
        ];
    }
}
