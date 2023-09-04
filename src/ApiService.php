<?php

namespace Rupadana\ApiService;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\Transformers\DefaultTransformer;
use Spatie\QueryBuilder\QueryBuilder;

class ApiService
{

    /**
     * Filament Resource
     */
    protected static string | null $resource = null;

    protected static string | null $groupRouteName = null;

    /**
     * Key Name for query Get 
     * This is used in conditions using slugs in the search
     *
     * @var string
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

    public static function routes()
    {

        $slug = static::getResource()::getSlug();

        $name = (string) str(static::$groupRouteName ?? $slug)
            ->replace('/', '.')
            ->append('.');

        Route::name(
            $name
        )
            ->prefix(static::$groupRouteName ?? $slug)
            ->group(function (Router $router) {
                static::allRoutes($router);
            });
    }


    public static function allRoutes(Router $router)
    {
    }
}
