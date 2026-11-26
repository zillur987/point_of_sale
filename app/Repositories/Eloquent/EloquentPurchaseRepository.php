<?php
namespace App\Repositories\Eloquent;
use App\Models\Purchase;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class EloquentPurchaseRepository implements PurchaseRepositoryInterface
{
 public function paginate(array $f): LengthAwarePaginator { return Purchase::query()->with(['supplier:id,name,company_name','warehouse:id,name','user:id,name'])->withCount('items')->when($f['search']??null,fn($q,$s)=>$q->where(fn($w)=>$w->where('purchase_no','like',"%$s%")->orWhereHas('supplier',fn($x)=>$x->where('name','like',"%$s%"))))->when($f['status']??null,fn($q,$v)=>$q->where('status',$v))->when($f['supplier_id']??null,fn($q,$v)=>$q->where('supplier_id',$v))->latest()->paginate($f['per_page']??15); }
 public function create(array $d): Purchase { return Purchase::create($d); }
 public function findForUpdate(int $id): ?Purchase { return Purchase::with('items')->lockForUpdate()->find($id); }
 public function find(int $id): ?Purchase { return Purchase::with(['supplier','warehouse','user','items'])->find($id); }
}
