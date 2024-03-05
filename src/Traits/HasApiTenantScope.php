<?php

namespace Rupadana\ApiService\Traits;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Rupadana\ApiService\ApiService;

trait HasTenantApiScope
{

    protected static function getOwnerRelationshipName()
    {
        return config('api-service.tenancy.owner_relationship_name');
    }

    protected static function getOwnerRelationshipId()
    {
        return static::getOwnerRelationshipName() . '_id';
    }

    public static function bootHasTenantApiScope()
    {
        if (
            Filament::hasTenancy() &&
            ApiService::isTenancyEnabled() &&
            ApiService::tenancyAwareness()
        ) {
            static::addGlobalScope(static::getOwnerRelationshipName(), function (Builder $query) {
                if (auth()->check()) {
                    $query->where(static::getOwnerRelationshipId(), request()->tenant);
                }
            });
        }

        if (
            Filament::hasTenancy() &&
            ApiService::isTenancyEnabled() &&
            !ApiService::tenancyAwareness()
        ) {
            static::addGlobalScope(static::getOwnerRelationshipName(), function (Builder $query) {
                if (auth()->check()) {
                    $query->whereBelongsTo(request()->user()->{Str::plural(static::getOwnerRelationshipName())});
                }
            });
        }
    }
}
