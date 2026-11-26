<?php
namespace App\Repositories\Contracts;
use Illuminate\Support\Collection;
interface PurchaseCatalogRepositoryInterface { public function search(string $term,int $limit=30): Collection; }
