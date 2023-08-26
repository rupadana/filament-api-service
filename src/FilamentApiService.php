<?php

namespace Rupadana\FilamentApiService;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Rupadana\FilamentApiService\Transformers\DefaultTransformer;
use Spatie\QueryBuilder\QueryBuilder;

class FilamentApiService
{

    /**
     * Filament Resource
     */
    protected static string | null $resource = null;

    protected static string | null $groupRouteName = null;

    protected static string | null $responseTransformer = DefaultTransformer::class;

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
                
                static::customRoutes($router);
                $router->get('/', [static::class, 'paginationQuery']);
                $router->get('/{keyValue}', [static::class, 'getQuery']);

                
            });
    }

    public static function customRoutes(Router $router) {

    }

    public static function getModel()
    {
        return static::getResource()::getModel();
    }

    public static function getQueryModel()
    {
        return static::getModel()::query();
    }

    /**
     * Pagination Query
     * Using spatie-query-builder
     *
     * @return AnonymousResourceCollection
     */
    public static function paginationQuery(): ?AnonymousResourceCollection
    {
        $model = static::getQueryModel();

        $query = QueryBuilder::for($model)
        ->allowedFields($model::$allowedFields ?? [])
        ->allowedFilters($model::$allowedFilters ?? [])
        ->paginate(request()->query('per_page'))
        ->appends(request()->query());


        if (static::$responseTransformer != null) return static::$responseTransformer::collection($query);

        return $query;
    }


    /**
     * Get Single Resource
     *
     * @param string|int $keyValue
     * @return json
     */
    public static function getQuery($keyValue)
    {
        $model = static::getQueryModel();

        $query = QueryBuilder::for($model->where(static::getKeyName(), $keyValue))
        ->first();

        if(!$query) return self::send404Response();


        if (static::$responseTransformer != null) $query =  new static::$responseTransformer($query);

        return static::sendSuccessResponse(
            $query
        );
    }

    public static function sendSuccessResponse($data)
    {
        return response()->json([
            'message' => 'OK',
            'data' => $data
        ]);
    }

    public static function send404Response() {
        return response()->json([
            'message' => 'Data not found'
        ], 404);
    }
}
