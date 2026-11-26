<?php

namespace App\Repositories\Contracts;

use App\Models\CustomerGroup;
use Illuminate\Support\Collection;

interface CustomerGroupRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): CustomerGroup;
    public function update(CustomerGroup $group, array $data): CustomerGroup;
    public function find(int $id): ?CustomerGroup;
}
