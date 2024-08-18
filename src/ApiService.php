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

    protected static ?string $versionPrefix = '';

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

        $versionPrefix = '';

        if (static::getApiVersionMethod() === 'path') {

            $transformers = static::getResource()::getApiTransformers();

            foreach ($transformers as $transKey => $transformer) {

                $versionPrefix = '{' . self::getApiVersionParameterName() . '}/';
                $namePrefix = str($transKey)->kebab() . '/';
                $name = (string) str($namePrefix . (static::$groupRouteName ?? $slug))
                    ->replace('/', '.')
                    ->append('.');

                static::generateResourceRoutes($panel, $name, $versionPrefix);
            }

        } else {

            $name = (string) str(static::$groupRouteName ?? $slug)
                ->replace('/', '.')
                ->append('.');

            static::generateResourceRoutes($panel, $name, $versionPrefix);
        }
    }

    public static function handlers(): array
    {
        return [];
    }

    public static function generateResourceRoutes(Panel $panel, string $name, ?string $versionPrefix)
    {

        $slug = static::getResource()::getSlug();

        $resourceRouteMiddlewares = static::useResourceMiddlewares() ? static::getResource()::getRouteMiddleware($panel) : [];

        Route::name($name)
            ->middleware($resourceRouteMiddlewares)
            ->prefix($versionPrefix . (static::$groupRouteName ?? $slug))
            ->group(function (Router $route) {
                foreach (static::handlers() as $key => $handler) {
                    app($handler)->route($route);
                }
            });
    }

    public static function isRoutePrefixedByPanel(): bool
    {
        return config('api-service.route.panel_prefix', true);
    }

    public static function useResourceMiddlewares(): bool
    {
        return config('api-service.route.use_resource_middlewares', false);
    }

    public static function getDefaultTransformerName(): string
    {
        return config('api-service.route.default_transformer_name', 'default');
    }

    public static function getApiVersionMethod(): string
    {
        return config('api-service.route.api_version_method');
    }

    public static function getApiVersionParameterName(): string
    {
        return config('api-service.route.api_version_parameter_name');
    }
}
