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


        $query = QueryBuilder::for($model)
            ->allowedFields($this->getAllowedFields() ?? [])
            ->allowedSorts($this->getAllowedSorts() ?? [])
            ->allowedFilters($this->getAllowedFilters() ?? [])
            ->allowedIncludes($this->getAllowedIncludes() ?? [])
            ->paginate(request()->query('per_page'))
            ->appends(request()->query());

        return static::getApiTransformer()::collection($query);
    }
}
