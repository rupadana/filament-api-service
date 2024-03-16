<?php

namespace Rupadana\ApiService\Http;

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Traits\HttpResponse;
use Rupadana\ApiService\Transformers\DefaultTransformer;

class Handlers
{
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
        return 'ability';
    }

    public static function getKebabClassName()
    {
        return str(str(static::class)->beforeLast('Handler')->explode('\\')->last())->kebab();
    }

    public static function stringifyAbility()
    {
        return implode(',', static::getAbility());
    }

    public static function getAbility(): array
    {
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

    public function getPanel(): Panel
    {
        return $this->panel;
    }

    protected static function getEloquentQuery()
    {
        $query = app(static::getModel())->query();

        if (static::isScopedToTenant() && ApiService::tenancyAwareness() && Filament::getCurrentPanel()) {
            $query = static::modifyTenantQuery($query);
        }

        return $query;
    }
}
