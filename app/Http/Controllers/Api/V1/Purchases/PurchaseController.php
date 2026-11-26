<?php
namespace App\Http\Controllers\Api\V1\Purchases;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchases\{PurchaseIndexRequest,StorePurchaseRequest};
use App\Http\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use App\Repositories\Contracts\{PurchaseCatalogRepositoryInterface,PurchaseRepositoryInterface};
use App\Services\Contracts\PurchaseServiceInterface;
use Illuminate\Http\Request;
class PurchaseController extends Controller
{
 public function __construct(private readonly PurchaseRepositoryInterface $purchases,private readonly PurchaseCatalogRepositoryInterface $catalog,private readonly PurchaseServiceInterface $service){}
 public function index(PurchaseIndexRequest $request){return PurchaseResource::collection($this->purchases->paginate($request->validated()));}
 public function catalog(Request $request){$data=$request->validate(['search'=>['nullable','string','max:100'],'limit'=>['nullable','integer','min:1','max:50']]);return response()->json(['data'=>$this->catalog->search($data['search']??'',(int)($data['limit']??30))]);}
 public function store(StorePurchaseRequest $request){$purchase=$this->service->createDraft($request->validated(),(int)$request->user()->id);return (new PurchaseResource($purchase))->response()->setStatusCode(201);}
 public function show(Purchase $purchase){$purchase=$this->purchases->find($purchase->id);return new PurchaseResource($purchase);}
 public function receive(Request $request,Purchase $purchase){$received=$this->service->receive($purchase->id,(int)$request->user()->id);return new PurchaseResource($received);}
}
