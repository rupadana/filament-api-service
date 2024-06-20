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

        if (!$record->isRelation($relationshipName)) {

            $resourceClass = static::class;
            $recordClass = $record::class;

            throw new Exception("The model [{$recordClass}] does not have a relationship named [{$relationshipName}]. You can change the relationship being used by passing it to the [ownershipRelationship] argument of the [tenant()] method in configuration. You can change the relationship being used per-resource by setting it as the [\$tenantOwnershipRelationshipName] static property on the [{$resourceClass}] resource class.");
        }

        return $record->{$relationshipName}();
    }

    protected static function modifyTenantQuery(Builder $query, ?Model $tenant = null): Builder
    {
        if (request()->routeIs('api.*')) {

            $reqPanel ??= request()->route()->parameter('panel');
            $tenantId ??= request()->route()->parameter('tenant');

            foreach (Filament::getPanels() as $panel) {
                if ($panel->getPath() == $reqPanel) {
                    Filament::setCurrentPanel($panel);
                    break;
                }
            }

            if (Filament::hasTenancy()) {

                $tenantOwnershipRelationship = static::getTenantOwnershipRelationship($query->getModel());
                $tenantOwnershipRelationshipName = static::getTenantOwnershipRelationshipName();
                $tenantModel = app(Filament::getTenantModel());

                if (
                    ApiService::isTenancyEnabled() &&
                    ApiService::tenancyAwareness() &&
                    static::isScopedToTenant() &&
                    $tenantId &&
                    $tenant = $tenantModel::where(Filament::getCurrentPanel()->getTenantSlugAttribute() ?? $tenantModel->getKeyName(), $tenantId)->first()
                ) {
                    if (auth()->check()) {

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
                    }
                }

                if (
                    ApiService::isTenancyEnabled() &&
                    !ApiService::tenancyAwareness() &&
                    static::isScopedToTenant()
                ) {

                    if (auth()->check()) {

                        $query = match (true) {

                            $tenantOwnershipRelationship instanceof MorphTo => $query
                                ->where($tenantModel->getRelationWithoutConstraints($tenantOwnershipRelationshipName)->getMorphType(), $tenantModel->getMorphClass())
                                ->whereIn($tenantModel->getRelationWithoutConstraints($tenantOwnershipRelationshipName)->getForeignKeyName(), request()->user()->{Str::plural($tenantOwnershipRelationshipName)}->pluck($tenantModel->getKeyName())->toArray()),
                            $tenantOwnershipRelationship instanceof BelongsTo => $query->whereBelongsTo(
                                request()->user()->{Str::plural($tenantOwnershipRelationshipName)}
                            ),
                            default => $query->whereHas(
                                $tenantOwnershipRelationshipName,
                                fn (Builder $query) => $query->whereKey($tenant->getKey()),
                            ),
                        };
                    }
                }
            }
        }

        return $query;
    }
}
