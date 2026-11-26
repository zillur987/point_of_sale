<?php
namespace App\Repositories\Eloquent;
use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
final class EloquentSaleRepository implements SaleRepositoryInterface {
 public function paginate(array $filters): LengthAwarePaginator { return Sale::query()->with(['warehouse:id,name','user:id,name'])->withCount('items')->when($filters['search']??null,fn($q,$v)=>$q->where(fn($w)=>$w->where('invoice_no','like',"%{$v}%")))->when($filters['date_from']??null,fn($q,$v)=>$q->whereDate('sold_at','>=',$v))->when($filters['date_to']??null,fn($q,$v)=>$q->whereDate('sold_at','<=',$v))->latest('id')->paginate(min((int)($filters['per_page']??15),100)); }
 public function create(array $attributes): Sale { return Sale::create($attributes); }
 public function findByRequestKey(string $key): ?Sale { return Sale::where('request_key',$key)->with(['items','payments','warehouse'])->first(); }
}
