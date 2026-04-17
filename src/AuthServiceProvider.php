<?php

namespace Rupadana\ApiService;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Rupadana\ApiService\Models\Token;
use Rupadana\ApiService\Policies\TokenPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::policy(Token::class, TokenPolicy::class);
    }
}
