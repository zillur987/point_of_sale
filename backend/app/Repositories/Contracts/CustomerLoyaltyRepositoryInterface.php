<?php

namespace App\Repositories\Contracts;

use App\Models\CustomerLoyaltyTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerLoyaltyRepositoryInterface
{
    public function paginateForCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator;
    public function create(array $data): CustomerLoyaltyTransaction;
}
