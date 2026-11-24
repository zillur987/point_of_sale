<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\BranchData;
use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final readonly class BranchService
{
    public function __construct(private BranchRepositoryInterface $branches) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->branches->paginate($perPage, $filters);
    }

    public function create(BranchData $data): Branch
    {
        return DB::transaction(fn () => $this->branches->create($data->toArray()));
    }

    public function update(Branch $branch, BranchData $data): Branch
    {
        return DB::transaction(fn () => $this->branches->update($branch, $data->toArray()));
    }

    public function delete(Branch $branch): void
    {
        $this->branches->delete($branch);
    }
}
