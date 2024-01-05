<?php

namespace Rupadana\ApiService\Tests\Fixtures\ProductApiService\Handlers;

use Rupadana\ApiService\Http\Handlers;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource;
use Spatie\QueryBuilder\QueryBuilder;

class DetailHandler extends Handlers
{
    public static ?string $uri = '/{id}';

    public static ?string $resource = ProductResource::class;

    public function handler($id)
    {
        $model = static::getModel()::query();

        $query = QueryBuilder::for(
            $model->where(static::getKeyName(), $id)
        )
            ->first();

        if (! $query) {
            return static::sendNotFoundResponse();
        }

        $transformer = static::getApiTransformer();

        return new $transformer($query);
    }
}
