<?php

namespace App\Services\Contracts;

use App\Models\Customer;

interface CustomerServiceInterface
{
    public function create(array $data): Customer;
    public function update(int $id, array $data): Customer;
}
