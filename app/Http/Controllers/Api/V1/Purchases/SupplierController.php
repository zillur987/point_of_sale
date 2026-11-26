<?php
namespace App\Http\Controllers\Api\V1\Purchases;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchases\SupplierRequest;
use App\Http\Resources\Purchases\SupplierResource;
use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Services\Purchases\SupplierService;
use Illuminate\Http\Request;
class SupplierController extends Controller
{
 public function __construct(private readonly SupplierRepositoryInterface $suppliers,private readonly SupplierService $service){}
 public function index(Request $request){$rows=$this->suppliers->paginate($request->only(['search','is_active','per_page']));return SupplierResource::collection($rows);}
 public function store(SupplierRequest $request){return (new SupplierResource($this->service->create($request->validated())))->response()->setStatusCode(201);}
 public function show(Supplier $supplier){return new SupplierResource($supplier);}
 public function update(SupplierRequest $request,Supplier $supplier){return new SupplierResource($this->service->update($supplier->id,$request->validated()));}
}
