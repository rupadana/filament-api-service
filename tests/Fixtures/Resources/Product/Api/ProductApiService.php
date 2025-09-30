<?php

namespace Rupadana\ApiService\Tests\Fixtures\Resources\Product\Api;

use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Tests\Fixtures\Resources\Product\Api\Handlers\CreateHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\Product\Api\Handlers\DeleteHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\Product\Api\Handlers\DetailHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\Product\Api\Handlers\PaginationHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\Product\Api\Handlers\UpdateHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\Product\ProductResource;

class ProductApiService extends ApiService
{
    protected static ?string $resource = ProductResource::class;
    protected static ?string $groupRouteName = 'our-products'; // customize route name

    public static function handlers(): array
    {

        return [
            CreateHandler::class,
            UpdateHandler::class,
            DeleteHandler::class,
            PaginationHandler::class,
            DetailHandler::class,
        ];
    }
}
