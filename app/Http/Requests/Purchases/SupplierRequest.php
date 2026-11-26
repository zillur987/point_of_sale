<?php
namespace App\Http\Requests\Purchases;
use Illuminate\Foundation\Http\FormRequest;
class SupplierRequest extends FormRequest
{
 public function authorize(): bool { return true; }
 public function rules(): array { return ['name'=>['required','string','max:150'],'company_name'=>['nullable','string','max:180'],'email'=>['nullable','email','max:180'],'phone'=>['nullable','string','max:40'],'tax_number'=>['nullable','string','max:80'],'address'=>['nullable','string','max:2000'],'city'=>['nullable','string','max:100'],'country'=>['nullable','string','max:100'],'notes'=>['nullable','string','max:4000'],'is_active'=>['sometimes','boolean']]; }
}
