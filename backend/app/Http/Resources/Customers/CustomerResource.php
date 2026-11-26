<?php

namespace App\Http\Resources\Customers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_group_id' => $this->customer_group_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'notes' => $this->notes,
            'points_balance' => $this->points_balance,
            'is_active' => $this->is_active,
            'loyalty_transactions_count' => $this->whenCounted('loyaltyTransactions'),
            'group' => new CustomerGroupResource($this->whenLoaded('group')),
            'created_at' => $this->created_at,
        ];
    }
}
