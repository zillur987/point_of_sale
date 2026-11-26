<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockTransferIndexRequest;
use App\Http\Requests\Inventory\StoreStockTransferRequest;
use App\Http\Resources\Inventory\StockTransferResource;
use App\Repositories\Contracts\StockTransferRepositoryInterface;
use App\Services\Inventory\StockTransferService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class StockTransferController extends Controller
{
    public function __construct(
        private readonly StockTransferRepositoryInterface $transfers,
        private readonly StockTransferService $service,
    ) {
    }

    public function index(StockTransferIndexRequest $request): AnonymousResourceCollection
    {
        return StockTransferResource::collection($this->transfers->paginate($request->filters(), $request->perPage()));
    }

    public function show(int $id): StockTransferResource
    {
        return new StockTransferResource($this->transfers->find($id) ?? abort(404));
    }

    public function store(StoreStockTransferRequest $request): JsonResponse
    {
        $transfer = $this->service->create($request->validated(), $request->user()?->id);

        return (new StockTransferResource($transfer))->response()->setStatusCode(201);
    }
}
