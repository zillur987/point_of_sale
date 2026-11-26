<?php
namespace App\Http\Requests\Sales;
use Illuminate\Foundation\Http\FormRequest;
class PosCatalogRequest extends FormRequest { public function authorize(): bool{return true;} public function rules(): array{return ['warehouse_id'=>['required','integer','exists:warehouses,id'],'search'=>['nullable','string','max:100']];} }
