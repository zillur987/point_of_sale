<?php
namespace App\Repositories\Contracts;
use Illuminate\Support\Collection;
interface PosCatalogRepositoryInterface { public function search(string $term,int $warehouseId,int $limit=30): Collection; }
