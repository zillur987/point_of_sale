<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Unit;

/** @extends BaseRepositoryInterface<Unit> */
interface UnitRepositoryInterface extends BaseRepositoryInterface
{
    public function hasProducts(int $id): bool;
}
