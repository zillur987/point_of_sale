<?php

namespace App\Providers;

use App\Models\Contact;
use App\Models\Deal;
use App\Policies\ContactPolicy;
use App\Policies\DealPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Interface => implementation. Swap here (or in tests) without touching services. */
    public array $bindings = [];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Paginator::useBootstrapFive();

        // Fail loudly on N+1 queries and typos in attribute names during development.
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
