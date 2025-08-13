<?php

namespace Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api;

use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api\Handlers\CreateHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api\Handlers\UpdateHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api\Handlers\DeleteHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api\Handlers\PaginationHandler;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api\Handlers\DetailHandler;
use Rupadana\ApiService\ApiService;

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
