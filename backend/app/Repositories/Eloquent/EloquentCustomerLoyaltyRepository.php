<?php

namespace App\Repositories\Eloquent;

use App\Models\CustomerLoyaltyTransaction;
use App\Repositories\Contracts\CustomerLoyaltyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCustomerLoyaltyRepository implements CustomerLoyaltyRepositoryInterface
{
    public function paginateForCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return CustomerLoyaltyTransaction::query()
            ->with('user:id,name')
            ->where('customer_id', $customerId)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): CustomerLoyaltyTransaction
    {
        return CustomerLoyaltyTransaction::create($data)->load('user:id,name');
    }
}
