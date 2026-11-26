<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\CustomerIndexRequest;
use App\Http\Requests\Customers\CustomerRequest;
use App\Http\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Services\Contracts\CustomerServiceInterface;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerServiceInterface $service
    ) {}

    public function index(CustomerIndexRequest $request)
    {
        return CustomerResource::collection(
            $this->customers->paginate($request->validated())
        );
    }

    public function store(CustomerRequest $request)
    {
        $customer = $this->service->create($request->validated());

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Customer $customer)
    {
        $customer->load('group')->loadCount('loyaltyTransactions');

        return new CustomerResource($customer);
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        return new CustomerResource(
            $this->service->update($customer->id, $request->validated())
        );
    }
}
