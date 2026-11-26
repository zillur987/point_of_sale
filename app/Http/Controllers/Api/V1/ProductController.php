<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            $this->service->list(
                $request->only(['search', 'category_id', 'brand_id', 'type', 'is_active']),
                min($request->integer('per_page', 15), 100)
            )
        );
    }

    public function store(ProductRequest $request): JsonResponse
    {
        return (new ProductResource($this->service->create($request->toDto())))
            ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($this->service->show($product));
    }

    public function update(ProductRequest $request, Product $product): ProductResource
    {
        return new ProductResource($this->service->update($product, $request->toDto()));
    }

    public function destroy(Product $product): Response
    {
        $this->service->delete($product);

        return response()->noContent();
    }
}
