<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Customer::query()
            ->with('group')
            ->withCount('loyaltyTransactions')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($where) use ($search) {
                    $where->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['customer_group_id']), fn ($query) => $query->where('customer_group_id', $filters['customer_group_id']))
            ->when(isset($filters['is_active']), fn ($query) => $query->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data)->load('group');
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh()->load('group');
    }

    public function find(int $id): ?Customer
    {
        return Customer::query()->with('group')->withCount('loyaltyTransactions')->find($id);
    }
}
