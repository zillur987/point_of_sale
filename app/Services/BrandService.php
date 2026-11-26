<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\BrandData;
use App\Exceptions\BusinessRuleException;
use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Support\SlugGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final readonly class BrandService
{
    public function __construct(
        private BrandRepositoryInterface $brands,
        private SlugGenerator $slugs,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->brands->paginate($perPage, $filters);
    }

    public function create(BrandData $data): Brand
    {
        return DB::transaction(fn () => $this->brands->create([
            'name' => $data->name,
            'slug' => $this->slugs->unique($data->name, fn (string $s) => $this->brands->slugExists($s)),
            'is_active' => $data->isActive,
        ]));
    }

    public function update(Brand $brand, BrandData $data): Brand
    {
        return DB::transaction(fn () => $this->brands->update($brand, [
            'name' => $data->name,
            'is_active' => $data->isActive,
        ]));
    }

    public function delete(Brand $brand): void
    {
        if ($this->brands->hasProducts($brand->id)) {
            throw new BusinessRuleException('This brand is used by products and cannot be deleted.');
        }

        $this->brands->delete($brand);
    }
}
