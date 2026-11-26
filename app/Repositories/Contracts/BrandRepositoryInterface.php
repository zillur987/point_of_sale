<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Brand;

/** @extends BaseRepositoryInterface<Brand> */
interface BrandRepositoryInterface extends BaseRepositoryInterface
{
    public function slugExists(string $slug, ?int $ignoreId = null): bool;

    public function hasProducts(int $id): bool;
}
