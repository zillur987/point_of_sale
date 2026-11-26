<?php
namespace App\Http\Resources\Purchases;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class PurchaseResource extends JsonResource
{
 public function toArray(Request $request): array{return ['id'=>$this->id,'purchase_no'=>$this->purchase_no,'status'=>$this->status->value,'subtotal'=>$this->subtotal,'discount_amount'=>$this->discount_amount,'tax_amount'=>$this->tax_amount,'grand_total'=>$this->grand_total,'note'=>$this->note,'ordered_at'=>$this->ordered_at,'received_at'=>$this->received_at,'items_count'=>$this->whenCounted('items'),'supplier'=>$this->whenLoaded('supplier',fn()=>['id'=>$this->supplier->id,'name'=>$this->supplier->name,'company_name'=>$this->supplier->company_name]),'warehouse'=>$this->whenLoaded('warehouse',fn()=>['id'=>$this->warehouse->id,'name'=>$this->warehouse->name]),'user'=>$this->whenLoaded('user',fn()=>['id'=>$this->user->id,'name'=>$this->user->name]),'items'=>$this->whenLoaded('items',fn()=>$this->items->map(fn($i)=>['id'=>$i->id,'product_variant_id'=>$i->product_variant_id,'product_name'=>$i->product_name,'variant_name'=>$i->variant_name,'sku'=>$i->sku,'quantity'=>(string)$i->quantity,'unit_cost'=>$i->unit_cost,'line_total'=>$i->line_total]))];}
}
