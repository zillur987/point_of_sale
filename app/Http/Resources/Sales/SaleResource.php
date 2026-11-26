<?php
namespace App\Http\Resources\Sales;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class SaleResource extends JsonResource {
 public function toArray(Request $request): array {return ['id'=>$this->id,'invoice_no'=>$this->invoice_no,'status'=>$this->status,'items_count'=>$this->when(isset($this->items_count),(int)$this->items_count),'subtotal'=>$this->subtotal,'discount_amount'=>$this->discount_amount,'tax_amount'=>$this->tax_amount,'grand_total'=>$this->grand_total,'paid_amount'=>$this->paid_amount,'change_amount'=>$this->change_amount,'sold_at'=>$this->sold_at,'warehouse'=>$this->warehouse?->only(['id','name']),'user'=>$this->user?->only(['id','name']),'items'=>$this->whenLoaded('items',fn()=> $this->items->map(fn($i)=>['id'=>$i->id,'product_variant_id'=>$i->product_variant_id,'product_name'=>$i->product_name,'variant_name'=>$i->variant_name,'sku'=>$i->sku,'quantity'=>(string)$i->quantity,'unit_price'=>$i->unit_price,'discount_amount'=>$i->discount_amount,'tax_amount'=>$i->tax_amount,'line_total'=>$i->line_total])),'payments'=>$this->whenLoaded('payments',fn()=> $this->payments->map(fn($p)=>['method'=>$p->method->value,'amount'=>$p->amount,'reference'=>$p->reference]))];}
}
