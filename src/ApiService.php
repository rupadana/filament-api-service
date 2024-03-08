<?php

namespace Rupadana\ApiService;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class ApiService
{
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

    public static function registerRoutes()
    {

        $slug = static::getResource()::getSlug();

        $name = (string) str(static::$groupRouteName ?? $slug)
            ->replace('/', '.')
            ->append('.');

        Route::name(
            $name
        )
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
}
