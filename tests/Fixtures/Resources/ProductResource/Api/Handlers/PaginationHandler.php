<?php

namespace Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api\Handlers;

use Rupadana\ApiService\Http\Handlers;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers
{
    public static ?string $uri = '/';
    public static ?string $resource = ProductResource::class;

    public function handler()
    {
        $model = static::getModel();

        $allowedFields = method_exists($model, 'getAllowedFields') && is_array($model::getAllowedFields())
            ? $model::getAllowedFields()
            : (property_exists($model, 'allowedFields') && is_array($model::$allowedFields)
                ? $model::$allowedFields
                : []);

        $allowedSorts = method_exists($model, 'getAllowedSorts') && is_array($model::getAllowedSorts())
            ? $model::getAllowedSorts()
            : (property_exists($model, 'allowedSorts') && is_array($model::$allowedSorts)
                ? $model::$allowedSorts
                : []);

        $allowedFilters = method_exists($model, 'getAllowedFilters') && is_array($model::getAllowedFilters())
            ? $model::getAllowedFilters()
            : (property_exists($model, 'allowedFilters') && is_array($model::$allowedFilters)
                ? $model::$allowedFilters
                : []);

        $query = QueryBuilder::for($model)
            ->allowedFields($allowedFields)
            ->allowedSorts($allowedSorts)
            ->allowedFilters($allowedFilters)
            ->paginate(request()->query('per_page'))
            ->appends(request()->query());

        return static::getApiTransformer()::collection($query);
    }
}
