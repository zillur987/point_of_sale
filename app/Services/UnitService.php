<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UnitData;
use App\Exceptions\BusinessRuleException;
use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class UnitService
{
    public function __construct(private UnitRepositoryInterface $units) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->units->paginate($perPage, $filters);
    }

    public function create(UnitData $data): Unit
    {
        return $this->units->create($data->toArray());
    }

    public function update(Unit $unit, UnitData $data): Unit
    {
        return $this->units->update($unit, $data->toArray());
    }

    public function delete(Unit $unit): void
    {
        if ($this->units->hasProducts($unit->id)) {
            throw new BusinessRuleException('This unit is used by products and cannot be deleted.');
        }

        $this->units->delete($unit);
    }
}
