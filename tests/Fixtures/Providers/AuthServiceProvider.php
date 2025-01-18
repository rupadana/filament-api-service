<?php

namespace Rupadana\ApiService\Tests\Fixtures\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Rupadana\ApiService\Tests\Fixtures\Models\Product;
use Rupadana\ApiService\Tests\Fixtures\Policies\ProductPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
