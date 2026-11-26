<?php
namespace App\Repositories\Eloquent;
use App\Models\{ProductVariant,StockLevel};
use App\Repositories\Contracts\PosCatalogRepositoryInterface;
use Illuminate\Support\Collection;
final class EloquentPosCatalogRepository implements PosCatalogRepositoryInterface {
 public function search(string $term,int $warehouseId,int $limit=30): Collection {
  return ProductVariant::query()->with(['product.unit'])->whereHas('product',fn($q)=>$q->where('track_stock',true))->where(fn($q)=>$q->where('sku','like',"%{$term}%")->orWhere('barcode',$term)->orWhereHas('product',fn($p)=>$p->where('name','like',"%{$term}%")))->limit($limit)->get()->map(function($v)use($warehouseId){$qty=(string)(StockLevel::query()->where('warehouse_id',$warehouseId)->where('product_variant_id',$v->id)->value('quantity')??'0.000');return ['id'=>$v->id,'name'=>$v->product?->name,'variant_name'=>$v->name,'sku'=>$v->sku,'barcode'=>$v->barcode,'sale_price'=>(int)$v->sale_price,'available_quantity'=>$qty,'allow_decimal'=>(bool)$v->product?->unit?->allow_decimal];});
 }
}
