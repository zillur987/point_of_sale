<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\StoreCustomerLoyaltyRequest;
use App\Http\Resources\Customers\CustomerLoyaltyTransactionResource;
use App\Models\Customer;
use App\Repositories\Contracts\CustomerLoyaltyRepositoryInterface;
use App\Services\Contracts\CustomerLoyaltyServiceInterface;

class CustomerLoyaltyController extends Controller
{
    public function __construct(
        private readonly CustomerLoyaltyRepositoryInterface $transactions,
        private readonly CustomerLoyaltyServiceInterface $service
    ) {}

    public function index(Customer $customer)
    {
        return CustomerLoyaltyTransactionResource::collection(
            $this->transactions->paginateForCustomer($customer->id)
        );
    }

    public function store(StoreCustomerLoyaltyRequest $request, Customer $customer)
    {
        $transaction = $this->service->apply(
            $customer->id,
            $request->validated(),
            (int) $request->user()->id
        );

        return (new CustomerLoyaltyTransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }
}
