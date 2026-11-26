<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class UnitController extends Controller
{
    public function __construct(private readonly UnitService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return UnitResource::collection(
            $this->service->list(
                $request->only(['search']),
                min($request->integer('per_page', 15), 100)
            )
        );
    }

    public function store(UnitRequest $request): JsonResponse
    {
        return (new UnitResource($this->service->create($request->toDto())))
            ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Unit $unit): UnitResource
    {
        return new UnitResource($unit);
    }

    public function update(UnitRequest $request, Unit $unit): UnitResource
    {
        return new UnitResource($this->service->update($unit, $request->toDto()));
    }

    public function destroy(Unit $unit): Response
    {
        $this->service->delete($unit);

        return response()->noContent();
    }
}
