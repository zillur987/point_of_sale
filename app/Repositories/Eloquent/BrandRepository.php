<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/** @extends BaseRepository<Brand> */
final class BrandRepository extends BaseRepository implements BrandRepositoryInterface
{
    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return $this->query()->withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q, int $id) => $q->whereKeyNot($id))
            ->exists();
    }

    public function hasProducts(int $id): bool
    {
        return $this->query()->whereKey($id)->whereHas('products')->exists();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $s) => $q->where('name', 'like', "%{$s}%"))
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOL))
            );
    }
}
