<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockIndexRequest;
use App\Http\Resources\Inventory\StockLevelResource;
use App\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    public function __construct(private readonly StockRepositoryInterface $stock)
    {
    }

    public function index(StockIndexRequest $request): AnonymousResourceCollection
    {
        return StockLevelResource::collection($this->stock->paginateLevels($request->filters(), $request->perPage()));
    }

    public function summary(StockIndexRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->stock->summary($request->filters())]);
    }
}
