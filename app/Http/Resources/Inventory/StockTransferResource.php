<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\StockTransfer */
class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $warehouse = fn ($w) => ['id' => $w->id, 'name' => $w->name, 'code' => $w->code];

        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'note' => $this->note,
            'transferred_at' => $this->transferred_at?->toDateString(),
            'items_count' => $this->whenCounted('items'),
            'from_warehouse' => $this->whenLoaded('fromWarehouse', fn () => $warehouse($this->fromWarehouse)),
            'to_warehouse' => $this->whenLoaded('toWarehouse', fn () => $warehouse($this->toWarehouse)),
            'user' => $this->whenLoaded('user', fn () => $this->user ? ['id' => $this->user->id, 'name' => $this->user->name] : null),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'variant' => new StockVariantResource($item->variant),
            ])),
        ];
    }
}
