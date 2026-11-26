<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Repositories\Eloquent\BranchRepository;
use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ProductVariantRepository;
use App\Repositories\Eloquent\UnitRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        BranchRepositoryInterface::class => BranchRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        BrandRepositoryInterface::class => BrandRepository::class,
        UnitRepositoryInterface::class => UnitRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        ProductVariantRepositoryInterface::class => ProductVariantRepository::class,
        // Add future repositories here
    ];
}
