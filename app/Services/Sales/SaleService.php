<?php
namespace App\Services\Sales;
use App\Enums\Inventory\MovementType;
use App\Enums\Sales\PaymentMethod;
use App\DataTransferObjects\Inventory\StockMovementData;
use App\Models\{ProductVariant,Sale,SaleItem,SalePayment};
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Services\Contracts\{SaleServiceInterface,StockServiceInterface};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
final class SaleService implements SaleServiceInterface {
 public function __construct(private readonly SaleRepositoryInterface $sales, private readonly StockServiceInterface $stock) {}
 public function checkout(array $data,int $userId): Sale {
  if($existing=$this->sales->findByRequestKey($data['request_key'])) return $existing;
  return DB::transaction(function() use($data,$userId) {
   $ids=array_column($data['items'],'product_variant_id');
   $variants=ProductVariant::query()->with('product.unit')->whereIn('id',$ids)->lockForUpdate()->get()->keyBy('id');
   if($variants->count()!==count(array_unique($ids))) throw ValidationException::withMessages(['items'=>'One or more products no longer exist.']);
   $subtotal=0; $rows=[];
   foreach($data['items'] as $item) {
    $variant=$variants->get($item['product_variant_id']); $qty=(float)$item['quantity'];
    if(!$variant || !$variant->product) throw ValidationException::withMessages(['items'=>'Invalid product variant.']);
    if(!$variant->product->track_stock) throw ValidationException::withMessages(['items'=>'A cart item does not track stock.']);
    if(!$variant->product->unit?->allow_decimal && floor($qty)!==$qty) throw ValidationException::withMessages(['items'=>'This product only allows whole quantities.']);
    $unitPrice=(int)$variant->sale_price; $lineSubtotal=(int)round($qty*$unitPrice);
    $lineDiscount=(int)($item['discount_amount']??0); $lineTax=(int)($item['tax_amount']??0);
    if($lineDiscount<0 || $lineDiscount>$lineSubtotal || $lineTax<0) throw ValidationException::withMessages(['items'=>'Invalid line discount or tax.']);
    $lineTotal=$lineSubtotal-$lineDiscount+$lineTax; $subtotal+=$lineSubtotal;
    $rows[]=['variant'=>$variant,'quantity'=>number_format($qty,3,'.',''),'unit_price'=>$unitPrice,'discount_amount'=>$lineDiscount,'tax_amount'=>$lineTax,'line_total'=>$lineTotal];
   }
   $discount=(int)($data['discount_amount']??0); $tax=(int)($data['tax_amount']??0);
   if($discount<0 || $discount>$subtotal || $tax<0) throw ValidationException::withMessages(['discount_amount'=>'Invalid discount or tax.']);
   $itemsDiscount=array_sum(array_column($rows,'discount_amount')); $itemsTax=array_sum(array_column($rows,'tax_amount'));
   $grand=max(0,$subtotal-$discount-$itemsDiscount+$tax+$itemsTax);
   $paid=(int)$data['paid_amount'];
   if($paid<$grand) throw ValidationException::withMessages(['paid_amount'=>'Payment is less than the amount due.']);
   $sale=$this->sales->create(['invoice_no'=>'POS-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),'warehouse_id'=>$data['warehouse_id'],'customer_id'=>$data['customer_id']??null,'user_id'=>$userId,'status'=>'completed','subtotal'=>$subtotal,'discount_amount'=>$discount+$itemsDiscount,'tax_amount'=>$tax+$itemsTax,'grand_total'=>$grand,'paid_amount'=>$paid,'change_amount'=>$paid-$grand,'note'=>$data['note']??null,'request_key'=>$data['request_key'],'sold_at'=>$data['sold_at']??now()]);
   foreach($rows as $row) {
    $v=$row['variant'];
    $sale->items()->create(['product_variant_id'=>$v->id,'product_name'=>$v->product->name,'variant_name'=>$v->name,'sku'=>$v->sku,'quantity'=>$row['quantity'],'unit_price'=>$row['unit_price'],'discount_amount'=>$row['discount_amount'],'tax_amount'=>$row['tax_amount'],'line_total'=>$row['line_total']]);
    $this->stock->move(new StockMovementData($data['warehouse_id'],$v->id,'-'.$row['quantity'],MovementType::Sale,$userId,$sale,'POS sale '.$sale->invoice_no));
   }
   $sale->payments()->create(['method'=>PaymentMethod::from($data['payment_method']),'amount'=>$grand,'reference'=>$data['payment_reference']??null]);
   return $sale->load(['items','payments','warehouse','user']);
  },3);
 }
}
