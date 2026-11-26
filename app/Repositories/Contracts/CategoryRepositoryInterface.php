<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;

/** @extends BaseRepositoryInterface<Category> */
interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function slugExists(string $slug, ?int $ignoreId = null): bool;

    public function hasChildren(int $id): bool;

    public function hasProducts(int $id): bool;
}
