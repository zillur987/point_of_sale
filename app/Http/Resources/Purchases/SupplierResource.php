<?php
namespace App\Http\Resources\Purchases;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class SupplierResource extends JsonResource
{
 public function toArray(Request $request): array{return ['id'=>$this->id,'name'=>$this->name,'company_name'=>$this->company_name,'email'=>$this->email,'phone'=>$this->phone,'tax_number'=>$this->tax_number,'address'=>$this->address,'city'=>$this->city,'country'=>$this->country,'notes'=>$this->notes,'is_active'=>$this->is_active,'created_at'=>$this->created_at];}
}
