<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentIndexRequest;
use App\Http\Requests\Inventory\StoreStockAdjustmentRequest;
use App\Http\Resources\Inventory\StockAdjustmentResource;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private readonly StockAdjustmentRepositoryInterface $adjustments,
        private readonly StockAdjustmentService $service,
    ) {
    }

    public function index(StockAdjustmentIndexRequest $request): AnonymousResourceCollection
    {
        return StockAdjustmentResource::collection($this->adjustments->paginate($request->filters(), $request->perPage()));
    }

    public function show(int $id): StockAdjustmentResource
    {
        return new StockAdjustmentResource($this->adjustments->find($id) ?? abort(404));
    }

    public function store(StoreStockAdjustmentRequest $request): JsonResponse
    {
        $adjustment = $this->service->create($request->validated(), $request->user()?->id);

        return (new StockAdjustmentResource($adjustment))->response()->setStatusCode(201);
    }
}
