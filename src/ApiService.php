<?php

namespace Rupadana\ApiService;

use Filament\Panel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\Concerns\HasTenancy;

class ApiService
{
    use HasTenancy;

    /**
     * Filament Resource
     */
    protected static ?string $resource = null;
    protected static ?string $groupRouteName = null;

    /**
     * Key Name for query Get
     * This is used in conditions using slugs in the search
     */
    protected static string $keyName = 'id';

    public static function getKeyName()
    {
        return static::$keyName;
    }

    public static function getResource()
    {
        return static::$resource;
    }

    public static function registerRoutes(Panel $panel)
    {

        $slug = static::getResource()::getSlug();

        $name = (string) str(static::$groupRouteName ?? $slug)
            ->replace('/', '.')
            ->append('.');

        $resourceRouteMiddlewares = static::useResourceMiddlewares() ? static::getResource()::getRouteMiddleware($panel) : [];

        Route::name($name)
            ->middleware($resourceRouteMiddlewares)
            ->prefix(static::$groupRouteName ?? $slug)
            ->group(function (Router $route) {
                static::handlers();

                foreach (static::handlers() as $key => $handler) {
                    app($handler)->route($route);
                }
            });
    }

    public static function handlers(): array
    {
        return [];
    }

    public static function isRoutePrefixedByPanel(): bool
    {
        return config('api-service.route.panel_prefix', true);
    }

    public static function useResourceMiddlewares(): bool
    {
        return config('api-service.route.use_resource_middlewares', false);
    }
}
