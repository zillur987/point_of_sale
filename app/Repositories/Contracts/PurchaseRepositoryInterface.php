<?php
namespace App\Repositories\Contracts;
use App\Models\{Purchase,Supplier};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface PurchaseRepositoryInterface
{
 public function paginate(array $filters): LengthAwarePaginator;
 public function create(array $data): Purchase;
 public function findForUpdate(int $id): ?Purchase;
 public function find(int $id): ?Purchase;
}
