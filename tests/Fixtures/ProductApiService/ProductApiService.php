<?php

namespace Rupadana\ApiService\Tests\Fixtures\ProductApiService;

use Illuminate\Routing\Router;
use Rupadana\ApiService\ApiService;

class ProductApiService extends ApiService
{
    protected static ?string $resource = \Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource::class;

    protected static ?string $groupRouteName = 'our-products'; // customize route name

    public static function allRoutes(Router $router)
    {
        Handlers\CreateHandler::route($router);
        Handlers\UpdateHandler::route($router);
        Handlers\DeleteHandler::route($router);
        Handlers\PaginationHandler::route($router);
        Handlers\DetailHandler::route($router);
    }
}
