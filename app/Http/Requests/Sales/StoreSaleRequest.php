<?php
namespace App\Http\Requests\Sales;
use Illuminate\Foundation\Http\FormRequest;
class StoreSaleRequest extends FormRequest {
 public function authorize(): bool{return true;}
 public function rules(): array{return [
 'warehouse_id'=>['required','integer','exists:warehouses,id'],'customer_id'=>['nullable','integer'],
 'request_key'=>['required','uuid'],'items'=>['required','array','min:1','max:100'],
 'items.*.product_variant_id'=>['required','integer','distinct','exists:product_variants,id'],
 'items.*.quantity'=>['required','numeric','gt:0','max:999999'],
 'items.*.discount_amount'=>['nullable','integer','min:0'],'items.*.tax_amount'=>['nullable','integer','min:0'],
 'discount_amount'=>['nullable','integer','min:0'],'tax_amount'=>['nullable','integer','min:0'],
 'paid_amount'=>['required','integer','min:0'],'payment_method'=>['required','in:cash,card,mobile_banking,bank'],
 'payment_reference'=>['nullable','string','max:120'],'note'=>['nullable','string','max:1000'],'sold_at'=>['nullable','date']
 ]; }
}
