<?php

namespace Rupadana\FilamentApiService;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\QueryBuilder;

class FilamentApiService
{

    /**
     * Filament Resource
     */
    protected static string | null $resource = null;

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

        Route::name(
            (string) str($slug)
                ->replace('/', '.')
                ->append('.')
        )
            ->prefix($slug)
            ->group(function () {
                Route::get('/', [static::class, 'paginationQuery']);
                Route::get('/{keyValue}', [static::class, 'getQuery']);
            });
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
     * @return LengthAwarePaginator
     */
    public static function paginationQuery(): ?LengthAwarePaginator
    {
        $model = static::getQueryModel();

        return QueryBuilder::for($model)
            ->allowedFields($model::$allowedFields ?? [])
            ->allowedFilters($model::$allowedFilters ?? [])
            ->paginate(request()->query('per_page'))
            ->appends(request()->query());
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

        return static::sendSuccessResponse(
            QueryBuilder::for($model->where(static::getKeyName(), $keyValue))
                ->first()
        );
    }

    public static function sendSuccessResponse($data)
    {
        return response()->json([
            'data' => $data
        ]);
    }
}
