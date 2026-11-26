<?php
namespace App\Services\Purchases;
use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Validation\ValidationException;
class SupplierService
{
 public function __construct(private readonly SupplierRepositoryInterface $suppliers) {}
 public function create(array $data): Supplier { return $this->suppliers->create($data); }
 public function update(int $id,array $data): Supplier { $supplier=$this->suppliers->find($id);if(!$supplier)throw ValidationException::withMessages(['supplier'=>'Supplier not found.']);return $this->suppliers->update($supplier,$data); }
}
