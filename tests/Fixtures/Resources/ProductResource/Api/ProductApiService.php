<?php

namespace Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api;

use Illuminate\Routing\Router;
use Rupadana\ApiService\ApiService;

class ProductApiService extends ApiService
{
    protected static ?string $resource = \Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource::class;

    protected static ?string $groupRouteName = 'our-products'; // customize route name

    public static function handlers(): array
    {

        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];
    }
}
