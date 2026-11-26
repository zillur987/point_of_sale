<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockMovementIndexRequest;
use App\Http\Resources\Inventory\StockMovementResource;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockMovementController extends Controller
{
    public function __construct(private readonly StockMovementRepositoryInterface $movements)
    {
    }

    public function index(StockMovementIndexRequest $request): AnonymousResourceCollection
    {
        return StockMovementResource::collection($this->movements->paginate($request->filters(), $request->perPage()));
    }
}
