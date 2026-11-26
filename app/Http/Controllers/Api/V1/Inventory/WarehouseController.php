<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\WarehouseIndexRequest;
use App\Http\Requests\Inventory\WarehouseRequest;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Services\Inventory\WarehouseService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $warehouses,
        private readonly WarehouseService $service,
    ) {
    }

    public function index(WarehouseIndexRequest $request): AnonymousResourceCollection
    {
        return WarehouseResource::collection($this->warehouses->paginate($request->filters(), $request->perPage()));
    }

    /** Active warehouses for dropdowns (not paginated). */
    public function options(): AnonymousResourceCollection
    {
        return WarehouseResource::collection($this->warehouses->options());
    }

    public function show(Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($warehouse);
    }

    public function store(WarehouseRequest $request): WarehouseResource
    {
        return new WarehouseResource($this->service->create($request->validated()));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($this->service->update($warehouse, $request->validated()));
    }

    public function destroy(Warehouse $warehouse): Response
    {
        $this->service->delete($warehouse);

        return response()->noContent();
    }
}
