<?php

namespace Rupadana\ApiService\Tests\Fixtures\ProductApiService\Handlers;

use Rupadana\ApiService\Http\Handlers;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = ProductResource::class;

    public function handler()
    {
        $model = static::getModel();

        $query = QueryBuilder::for($model)
            ->allowedFields($model::$allowedFields ?? [])
            ->allowedSorts($model::$allowedSorts ?? [])
            ->allowedFilters($model::$allowedFilters ?? [])
            ->paginate(request()->query('per_page'))
            ->appends(request()->query());

        return static::getApiTransformer()::collection($query);
    }
}

