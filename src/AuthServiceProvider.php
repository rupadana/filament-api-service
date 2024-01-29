<?php

namespace Rupadana\ApiService;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Rupadana\ApiService\Models\Token;
use Rupadana\ApiService\Policies\TokenPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Token::class => TokenPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
    }
}
