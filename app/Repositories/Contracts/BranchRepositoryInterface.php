<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Branch;

/** @extends BaseRepositoryInterface<Branch> */
interface BranchRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?Branch;
}
