<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class BranchController extends Controller
{
    public function __construct(private readonly BranchService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return BranchResource::collection(
            $this->service->list(
                $request->only(['search', 'is_active']),
                min($request->integer('per_page', 15), 100)
            )
        );
    }

    public function store(BranchRequest $request): JsonResponse
    {
        $branch = $this->service->create($request->toDto());

        return (new BranchResource($branch))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Branch $branch): BranchResource
    {
        return new BranchResource($branch);
    }

    public function update(BranchRequest $request, Branch $branch): BranchResource
    {
        return new BranchResource($this->service->update($branch, $request->toDto()));
    }

    public function destroy(Branch $branch): Response
    {
        $this->service->delete($branch);

        return response()->noContent();
    }
}
