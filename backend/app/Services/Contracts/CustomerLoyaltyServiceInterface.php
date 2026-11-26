<?php

namespace App\Services\Contracts;

use App\Models\CustomerLoyaltyTransaction;

interface CustomerLoyaltyServiceInterface
{
    public function apply(int $customerId, array $data, int $userId): CustomerLoyaltyTransaction;
}
