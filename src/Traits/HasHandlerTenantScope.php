<?php

namespace Rupadana\ApiService\Traits;

use Exception;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Rupadana\ApiService\ApiService;

trait HasHandlerTenantScope
{
    protected static ?string $tenantOwnershipRelationshipName = null;

    public static function getTenantOwnershipRelationshipName(): string
    {
        return static::$tenantOwnershipRelationshipName ?? Filament::getTenantOwnershipRelationshipName();
    }

    protected static function getOwnerRelationshipId()
    {
        return static::getOwnerRelationshipName() . '_' . app(static::getModel())->getKeyName();
    }

    public static function getTenantOwnershipRelationship(Model $record): Relation
    {
        $relationshipName = static::getTenantOwnershipRelationshipName();

        if (! $record->isRelation($relationshipName)) {

            $resourceClass = static::class;
            $recordClass = $record::class;

            throw new Exception("The model [{$recordClass}] does not have a relationship named [{$relationshipName}]. You can change the relationship being used by passing it to the [ownershipRelationship] argument of the [tenant()] method in configuration. You can change the relationship being used per-resource by setting it as the [\$tenantOwnershipRelationshipName] static property on the [{$resourceClass}] resource class.");
        }

        return $record->{$relationshipName}();
    }

    protected static function modifyTenantQuery(Builder $query, ?Model $tenant = null): Builder
    {
        // Early return if not on API routes or tenancy not enabled
        if (! request()->routeIs('api.*') || ! Filament::hasTenancy()) {
            return $query;
        }

        // Early return if tenancy not enabled or not scoped to tenant
        if (! ApiService::isTenancyEnabled() || ! static::isScopedToTenant()) {
            return $query;
        }

        // Early return if user not authenticated
        if (! auth()->check()) {
            return $query;
        }

        $tenantOwnershipRelationship = static::getTenantOwnershipRelationship($query->getModel());
        $tenantOwnershipRelationshipName = static::getTenantOwnershipRelationshipName();
        $tenantModel = app(Filament::getTenantModel());

        if (ApiService::tenancyAwareness()) {
            $tenantId ??= request()->route()->parameter('tenant');

            if (! $tenantId) {
                return $query;
            }

            $tenant = $tenantModel::where(Filament::getCurrentOrDefaultPanel()->getTenantSlugAttribute() ?? $tenantModel->getRouteKeyName(), $tenantId)->first();

            if (! $tenant) {
                return $query;
            }

            $query = match (true) {
                $tenantOwnershipRelationship instanceof MorphTo => $query->whereMorphedTo(
                    $tenantOwnershipRelationshipName,
                    $tenant,
                ),
                $tenantOwnershipRelationship instanceof BelongsTo => $query->whereBelongsTo(
                    $tenant,
                    $tenantOwnershipRelationshipName,
                ),
                default => $query->whereHas(
                    $tenantOwnershipRelationshipName,
                    fn (Builder $query) => $query->whereKey($tenant->getKey()),
                ),
            };
        } else {
            $userTenants = request()->user()->{Str::plural($tenantOwnershipRelationshipName)};

            $query = match (true) {
                $tenantOwnershipRelationship instanceof MorphTo => $query
                    ->where($tenantModel->getRelationWithoutConstraints($tenantOwnershipRelationshipName)->getMorphType(), $tenantModel->getMorphClass())
                    ->whereIn($tenantModel->getRelationWithoutConstraints($tenantOwnershipRelationshipName)->getForeignKeyName(), $userTenants->pluck($tenantModel->getKeyName())->toArray()),
                $tenantOwnershipRelationship instanceof BelongsTo => $query->whereBelongsTo($userTenants),
                default => $query->whereHas(
                    $tenantOwnershipRelationshipName,
                    fn (Builder $query) => $query->whereIn($query->getModel()->getQualifiedKeyName(), $userTenants->pluck($tenantModel->getKeyName())),
                ),
            };
        }

        return $query;
    }
}
