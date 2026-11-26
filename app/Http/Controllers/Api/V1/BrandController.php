<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class BrandController extends Controller
{
    public function __construct(private readonly BrandService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return BrandResource::collection(
            $this->service->list(
                $request->only(['search', 'is_active']),
                min($request->integer('per_page', 15), 100)
            )
        );
    }

    public function store(BrandRequest $request): JsonResponse
    {
        return (new BrandResource($this->service->create($request->toDto())))
            ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Brand $brand): BrandResource
    {
        return new BrandResource($brand);
    }

    public function update(BrandRequest $request, Brand $brand): BrandResource
    {
        return new BrandResource($this->service->update($brand, $request->toDto()));
    }

    public function destroy(Brand $brand): Response
    {
        $this->service->delete($brand);

        return response()->noContent();
    }
}
