<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\CustomerGroupRequest;
use App\Http\Resources\Customers\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Repositories\Contracts\CustomerGroupRepositoryInterface;

class CustomerGroupController extends Controller
{
    public function __construct(
        private readonly CustomerGroupRepositoryInterface $groups
    ) {}

    public function index()
    {
        return CustomerGroupResource::collection($this->groups->all());
    }

    public function store(CustomerGroupRequest $request)
    {
        $group = $this->groups->create($request->validated());

        return (new CustomerGroupResource($group))
            ->response()
            ->setStatusCode(201);
    }

    public function update(CustomerGroupRequest $request, CustomerGroup $customerGroup)
    {
        return new CustomerGroupResource(
            $this->groups->update($customerGroup, $request->validated())
        );
    }
}
