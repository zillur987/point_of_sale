<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/** @extends BaseRepository<Unit> */
final class UnitRepository extends BaseRepository implements UnitRepositoryInterface
{
    public function __construct(Unit $model)
    {
        parent::__construct($model);
    }

    public function hasProducts(int $id): bool
    {
        return $this->query()->whereKey($id)->whereHas('products')->exists();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $query->when(
            $filters['search'] ?? null,
            fn (Builder $q, string $s) => $q->where(
                fn (Builder $w) => $w->where('name', 'like', "%{$s}%")->orWhere('short_name', 'like', "%{$s}%")
            )
        );
    }
}
