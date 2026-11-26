<?php
namespace App\Repositories\Contracts;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface SaleRepositoryInterface { public function paginate(array $filters): LengthAwarePaginator; public function create(array $attributes): Sale; public function findByRequestKey(string $key): ?Sale; }
