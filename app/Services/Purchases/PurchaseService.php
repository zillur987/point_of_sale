<?php
namespace App\Services\Purchases;
use App\DataTransferObjects\Inventory\StockMovementData;
use App\Enums\Inventory\MovementType;
use App\Enums\Purchases\PurchaseStatus;
use App\Models\{ProductVariant,Purchase};
use App\Repositories\Contracts\{PurchaseRepositoryInterface,SupplierRepositoryInterface};
use App\Services\Contracts\{PurchaseServiceInterface,StockServiceInterface};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
final class PurchaseService implements PurchaseServiceInterface
{
 public function __construct(private readonly PurchaseRepositoryInterface $purchases,private readonly SupplierRepositoryInterface $suppliers,private readonly StockServiceInterface $stock) {}
 public function createDraft(array $data,int $userId): Purchase {
  return DB::transaction(function()use($data,$userId){
   $supplier=$this->suppliers->find((int)$data['supplier_id']);
   if(!$supplier || !$supplier->is_active) throw ValidationException::withMessages(['supplier_id'=>'Select an active supplier.']);
   $ids=array_column($data['items'],'product_variant_id');
   $variants=ProductVariant::query()->with('product.unit')->whereIn('id',$ids)->lockForUpdate()->get()->keyBy('id');
   if($variants->count()!==count(array_unique($ids))) throw ValidationException::withMessages(['items'=>'One or more product variants do not exist.']);
   $subtotal=0;$rows=[];
   foreach($data['items'] as $item){
    $v=$variants->get((int)$item['product_variant_id']);$qty=(float)$item['quantity'];$cost=(int)$item['unit_cost'];
    if(!$v || !$v->product || !$v->product->track_stock) throw ValidationException::withMessages(['items'=>'Only stock-tracked products can be purchased.']);
    if($qty<=0 || $cost<0) throw ValidationException::withMessages(['items'=>'Quantity must be positive and unit cost cannot be negative.']);
    if(!$v->product->unit?->allow_decimal && floor($qty)!==$qty) throw ValidationException::withMessages(['items'=>'This product only allows whole quantities.']);
    $line=(int)round($qty*$cost);$subtotal+=$line;
    $rows[]=['variant'=>$v,'quantity'=>number_format($qty,3,'.',''),'unit_cost'=>$cost,'line_total'=>$line];
   }
   $discount=(int)($data['discount_amount']??0);$tax=(int)($data['tax_amount']??0);
   if($discount<0 || $discount>$subtotal || $tax<0) throw ValidationException::withMessages(['discount_amount'=>'Invalid discount or tax amount.']);
   $purchase=$this->purchases->create(['purchase_no'=>'PO-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),'supplier_id'=>$supplier->id,'warehouse_id'=>$data['warehouse_id'],'user_id'=>$userId,'status'=>PurchaseStatus::Draft,'subtotal'=>$subtotal,'discount_amount'=>$discount,'tax_amount'=>$tax,'grand_total'=>$subtotal-$discount+$tax,'note'=>$data['note']??null,'ordered_at'=>$data['ordered_at']??now()]);
   foreach($rows as $row){$v=$row['variant'];$purchase->items()->create(['product_variant_id'=>$v->id,'product_name'=>$v->product->name,'variant_name'=>$v->name,'sku'=>$v->sku,'quantity'=>$row['quantity'],'unit_cost'=>$row['unit_cost'],'line_total'=>$row['line_total']]);}
   return $purchase->load(['supplier','warehouse','items','user']);
  },3);
 }
 public function receive(int $purchaseId,int $userId): Purchase {
  return DB::transaction(function()use($purchaseId,$userId){
   $purchase=$this->purchases->findForUpdate($purchaseId);
   if(!$purchase) throw ValidationException::withMessages(['purchase'=>'Purchase not found.']);
   if($purchase->status!==PurchaseStatus::Draft) throw ValidationException::withMessages(['status'=>'Only draft purchases can be received.']);
   foreach($purchase->items as $item){
    $this->stock->move(new StockMovementData((int)$purchase->warehouse_id,(int)$item->product_variant_id,(string)$item->quantity,MovementType::Purchase,$userId,$purchase,'Goods received for '.$purchase->purchase_no));
   }
   $purchase->status=PurchaseStatus::Received;$purchase->received_at=now();$purchase->save();
   return $purchase->load(['supplier','warehouse','items','user']);
  },3);
 }
}
