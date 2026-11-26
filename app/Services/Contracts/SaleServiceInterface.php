<?php
namespace App\Services\Contracts;
use App\Models\Sale;
interface SaleServiceInterface { public function checkout(array $data, int $userId): Sale; }
