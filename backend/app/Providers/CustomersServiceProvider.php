<?php

namespace App\Providers;

use App\Repositories\Contracts\CustomerGroupRepositoryInterface;
use App\Repositories\Contracts\CustomerLoyaltyRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Eloquent\EloquentCustomerGroupRepository;
use App\Repositories\Eloquent\EloquentCustomerLoyaltyRepository;
use App\Repositories\Eloquent\EloquentCustomerRepository;
use App\Services\Contracts\CustomerLoyaltyServiceInterface;
use App\Services\Contracts\CustomerServiceInterface;
use App\Services\Customers\CustomerLoyaltyService;
use App\Services\Customers\CustomerService;
use Illuminate\Support\ServiceProvider;

class CustomersServiceProvider extends ServiceProvider
{
    public array $bindings = [
        CustomerRepositoryInterface::class => EloquentCustomerRepository::class,
        CustomerGroupRepositoryInterface::class => EloquentCustomerGroupRepository::class,
        CustomerLoyaltyRepositoryInterface::class => EloquentCustomerLoyaltyRepository::class,
        CustomerServiceInterface::class => CustomerService::class,
        CustomerLoyaltyServiceInterface::class => CustomerLoyaltyService::class,
    ];
}
