<?php
namespace App\Http\Requests\Purchases;
use Illuminate\Foundation\Http\FormRequest;
class StorePurchaseRequest extends FormRequest
{
 public function authorize(): bool { return true; }
 public function rules(): array { return ['supplier_id'=>['required','integer','exists:suppliers,id'],'warehouse_id'=>['required','integer','exists:warehouses,id'],'items'=>['required','array','min:1'],'items.*.product_variant_id'=>['required','integer','distinct','exists:product_variants,id'],'items.*.quantity'=>['required','numeric','gt:0','max:999999'],'items.*.unit_cost'=>['required','integer','min:0'],'discount_amount'=>['nullable','integer','min:0'],'tax_amount'=>['nullable','integer','min:0'],'note'=>['nullable','string','max:4000'],'ordered_at'=>['nullable','date']]; }
}
