<?php

namespace App\Repositories\Eloquent;

use App\Models\CustomerGroup;
use App\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentCustomerGroupRepository implements CustomerGroupRepositoryInterface
{
    public function all(): Collection
    {
        return CustomerGroup::query()->withCount('customers')->orderBy('name')->get();
    }

    public function create(array $data): CustomerGroup
    {
        return CustomerGroup::create($data);
    }

    public function update(CustomerGroup $group, array $data): CustomerGroup
    {
        $group->update($data);

        return $group->refresh();
    }

    public function find(int $id): ?CustomerGroup
    {
        return CustomerGroup::find($id);
    }
}
