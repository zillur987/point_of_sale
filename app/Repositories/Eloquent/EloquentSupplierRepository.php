<?php
namespace App\Repositories\Eloquent;
use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class EloquentSupplierRepository implements SupplierRepositoryInterface
{
 public function paginate(array $f): LengthAwarePaginator { return Supplier::query()->when($f['search']??null,fn($q,$s)=>$q->where(fn($w)=>$w->where('name','like',"%$s%")->orWhere('company_name','like',"%$s%")->orWhere('phone','like',"%$s%")->orWhere('email','like',"%$s%")))->when(isset($f['is_active']),fn($q)=>$q->where('is_active',$f['is_active']))->latest()->paginate($f['per_page']??15); }
 public function create(array $d): Supplier { return Supplier::create($d); }
 public function update(Supplier $s,array $d): Supplier { $s->update($d);return $s->refresh(); }
 public function find(int $id): ?Supplier { return Supplier::find($id); }
}
