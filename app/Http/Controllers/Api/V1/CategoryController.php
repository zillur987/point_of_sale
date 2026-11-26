<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            $this->service->list(
                $request->only(['search', 'parent_id', 'is_active']),
                min($request->integer('per_page', 15), 100)
            )
        );
    }

    public function tree(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->service->tree());
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        return (new CategoryResource($this->service->create($request->toDto())))
            ->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category->load('parent'));
    }

    public function update(CategoryRequest $request, Category $category): CategoryResource
    {
        return new CategoryResource($this->service->update($category, $request->toDto()));
    }

    public function destroy(Category $category): Response
    {
        $this->service->delete($category);

        return response()->noContent();
    }
}
