<?php
namespace App\Http\Requests\Purchases;
use Illuminate\Foundation\Http\FormRequest;
class PurchaseIndexRequest extends FormRequest
{
 public function authorize(): bool{return true;}
 public function rules(): array{return ['search'=>['nullable','string','max:100'],'status'=>['nullable','in:draft,received,cancelled'],'supplier_id'=>['nullable','integer','exists:suppliers,id'],'per_page'=>['nullable','integer','min:1','max:100']];}
}
