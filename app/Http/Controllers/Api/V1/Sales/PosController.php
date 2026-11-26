<?php
namespace App\Http\Controllers\Api\V1\Sales;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\{PosCatalogRequest,StoreSaleRequest};
use App\Http\Resources\Sales\SaleResource;
use App\Models\Sale;
use App\Repositories\Contracts\{PosCatalogRepositoryInterface,SaleRepositoryInterface};
use App\Services\Contracts\SaleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class PosController extends Controller {
 public function __construct(private readonly PosCatalogRepositoryInterface $catalog,private readonly SaleServiceInterface $service,private readonly SaleRepositoryInterface $sales){}
 public function catalog(PosCatalogRequest $request): JsonResponse { $d=$request->validated(); return response()->json(['data'=>$this->catalog->search($d['search']??'',(int)$d['warehouse_id'])]);}
 public function checkout(StoreSaleRequest $request): SaleResource {return new SaleResource($this->service->checkout($request->validated(),(int)$request->user()->id));}
 public function index(Request $request){return SaleResource::collection($this->sales->paginate($request->validate(['search'=>'nullable|string','date_from'=>'nullable|date','date_to'=>'nullable|date','per_page'=>'nullable|integer|min:1|max:100'])));}
 public function show(int $sale): SaleResource {return new SaleResource(Sale::with(['items','payments','warehouse','user'])->findOrFail($sale));}
}
