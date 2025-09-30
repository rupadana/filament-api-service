<?php

namespace Rupadana\ApiService\Http;

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Contracts\HasAllowedFields;
use Rupadana\ApiService\Contracts\HasAllowedFilters;
use Rupadana\ApiService\Contracts\HasAllowedIncludes;
use Rupadana\ApiService\Contracts\HasAllowedSorts;
use Rupadana\ApiService\Traits\HasHandlerTenantScope;
use Rupadana\ApiService\Traits\HttpResponse;
use Rupadana\ApiService\Transformers\DefaultTransformer;

class Handlers
{
    use HasHandlerTenantScope;
    use HttpResponse;
    protected Panel $panel;
    public static ?string $uri = '/';
    public static string $method = 'get';
    public static ?string $resource = null;
    protected static string $keyName = 'id';
    protected static bool $public = false;
    const POST = 'post';
    const GET = 'get';
    const DELETE = 'delete';
    const PATCH = 'patch';
    const PUT = 'put';

    public function __construct()
    {
        if (request()->routeIs('api.*') && ApiService::isRoutePrefixedByPanel()) {
            $this->panel(Filament::getPanel(request()->route()->parameter('panel')));
            Filament::setCurrentPanel($this->getPanel());
        }
    }

    public static function getMethod()
    {
        return static::$method;
    }

    public static function route(Router $router)
    {
        $method = static::getMethod();

        $router
            ->{$method}(static::$uri, [static::class, 'handler'])
            ->name(static::getKebabClassName())
            ->middleware(static::getRouteMiddleware());
    }

    public static function isPublic(): bool
    {
        return static::$public;
    }

    public static function getRouteMiddleware(): array
    {
        if (static::isPublic()) {
            return [];
        }

        return [
            'auth:sanctum',
            static::getMiddlewareAliasName() . ':' . static::stringifyAbility(),
        ];
    }

    protected static function getMiddlewareAliasName()
    {
        if (config('api-service.use-spatie-permission-middleware', false)) {
            return 'permission';
        }

        return 'ability';
    }

    public static function getKebabClassName()
    {
        if (config('api-service.use-spatie-permission-middleware', false)) {
            return match ($operation = str(str(static::class)->beforeLast('Handler')->explode('\\')->last())->kebab()->value()) {
                'detail' => 'view',
                'pagination' => 'view_any',
                default => $operation
            };
        }

        return str(str(static::class)->beforeLast('Handler')->explode('\\')->last())->kebab();
    }

    public static function stringifyAbility()
    {
        return implode(',', static::getAbility());
    }

    public static function getAbility(): array
    {
        if (config('api-service.use-spatie-permission-middleware', false)) {
            return [
                static::$permission,
            ];
        }

        return [
            str(str(static::getModel())->explode('\\')->last())->kebab() . ':' . static::getKebabClassName(),
        ];
    }

    public static function getModel()
    {
        return static::$resource::getModel();
    }

    public static function getApiTransformer(): ?string
    {
        if (! method_exists(static::$resource, 'getApiTransformer')) {
            return DefaultTransformer::class;
        }

        return static::$resource::getApiTransformer();
    }

    public static function getKeyName(): ?string
    {
        return static::$keyName;
    }

    public static function getTenantOwnershipRelationship(Model $record): Relation
    {
        return static::$resource::getTenantOwnershipRelationship($record);
    }

    public static function getTenantOwnershipRelationshipName(): ?string
    {
        return static::$resource::getTenantOwnershipRelationshipName();
    }

    public static function isScopedToTenant(): bool
    {
        return static::$resource::isScopedToTenant();
    }

    public function panel(Panel $panel)
    {
        $this->panel = $panel;

        return $this;
    }

    public function getAllowedFields(): array
    {

        $model = static::getModel();

        if (in_array(HasAllowedFields::class, class_implements($model))) {
            return static::getModel()::getAllowedFields();
        }

        if (property_exists($model, 'allowedFields') && is_array($model::$allowedFields)) {
            return $model::$allowedFields;
        }

        return [];
    }

    public function getAllowedIncludes(): array
    {
        $model = static::getModel();

        if (in_array(HasAllowedIncludes::class, class_implements($model))) {
            return $model::getAllowedIncludes();
        }

        if (property_exists($model, 'allowedIncludes') && is_array($model::$allowedIncludes)) {
            return $model::$allowedIncludes;
        }

        return [];
    }

    public function getAllowedSorts(): array
    {
        $model = static::getModel();

        if (in_array(HasAllowedSorts::class, class_implements($model))) {
            return $model::getAllowedSorts();
        }

        if (property_exists($model, 'allowedFields') && is_array($model::$allowedFields)) {
            return $model::$allowedFields;
        }

        return [];
    }

    public function getAllowedFilters(): array
    {
        $model = static::getModel();

        if (in_array(HasAllowedFilters::class, class_implements($model))) {
            return $model::getAllowedFilters();
        }

        if (property_exists($model, 'allowedFilters') && is_array($model::$allowedFilters)) {
            return $model::$allowedFilters;
        }

        return [];
    }

    public function getPanel(): Panel
    {
        return $this->panel;
    }

    protected static function getEloquentQuery()
    {
        $query = app(static::getModel())->query();

        if (static::isScopedToTenant() && ApiService::tenancyAwareness() && Filament::getCurrentOrDefaultPanel()) {
            $query = static::modifyTenantQuery($query);
        }

        return $query;
    }
}
