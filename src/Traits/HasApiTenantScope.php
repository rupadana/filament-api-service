<?php

namespace Rupadana\ApiService\Traits;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Str;

trait HasApiTenantScope
{
    public static function bootHasApiTenantScope()
    {

        if (request()->routeIs('api.*')) {
            if (
                Filament::hasTenancy() &&
                config('api-service.tenancy.enabled') &&
                config('api-service.tenancy.is_tenant_aware')
            ) {
                static::addGlobalScope(config('api-service.tenancy.tenant_ownership_relationship_name'), function (Builder $query) {
                    if (auth()->check()) {
                        $query->where(config('api-service.tenancy.tenant_ownership_relationship_name') . '_id', request()->tenant);
                    }
                });
            }

            if (
                Filament::hasTenancy() &&
                config('api-service.tenancy.enabled') &&
                ! config('api-service.tenancy.is_tenant_aware')
            ) {
                static::addGlobalScope(config('api-service.tenancy.tenant_ownership_relationship_name'), function (Builder $query) {
                    if (auth()->check()) {
                        $query->whereBelongsTo(request()->user()->{Str::plural(config('api-service.tenancy.tenant_ownership_relationship_name'))});
                    }
                });
            }
        }
    }
}
