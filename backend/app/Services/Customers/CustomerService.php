<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Services\Contracts\CustomerServiceInterface;
use Illuminate\Validation\ValidationException;

class CustomerService implements CustomerServiceInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers
    ) {}

    public function create(array $data): Customer
    {
        return $this->customers->create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = $this->customers->find($id);

        if (!$customer) {
            throw ValidationException::withMessages([
                'customer' => 'Customer not found.',
            ]);
        }

        return $this->customers->update($customer, $data);
    }
}
