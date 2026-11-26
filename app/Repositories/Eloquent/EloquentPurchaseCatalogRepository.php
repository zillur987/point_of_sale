<?php
namespace App\Repositories\Eloquent;
use App\Models\ProductVariant;
use App\Repositories\Contracts\PurchaseCatalogRepositoryInterface;
use Illuminate\Support\Collection;
class EloquentPurchaseCatalogRepository implements PurchaseCatalogRepositoryInterface
{
 public function search(string $term,int $limit=30): Collection { return ProductVariant::query()->with('product:id,name,track_stock')->whereHas('product',fn($q)=>$q->where('track_stock',true))->when($term!=='',fn($q)=>$q->where(fn($w)=>$w->where('sku','like',"%$term%")->orWhere('barcode','like',"%$term%")->orWhereHas('product',fn($p)=>$p->where('name','like',"%$term%"))))->limit(min(max($limit,1),50))->get()->map(fn($v)=>['id'=>$v->id,'product_name'=>$v->product?->name,'variant_name'=>$v->name,'sku'=>$v->sku,'purchase_price'=>(int)($v->purchase_price??0)]); }
}
