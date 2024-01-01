<?php

namespace Rupadana\ApiService\Tests\Fixtures\ProductApiService;

use Rupadana\ApiService\ApiService;
use Illuminate\Routing\Router;


class ProductApiService extends ApiService
{
    protected static string | null $resource = \Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource::class;

    protected static string | null $groupRouteName = 'our-products'; // customize route name

    public static function allRoutes(Router $router)
    {
        Handlers\CreateHandler::route($router);
        Handlers\UpdateHandler::route($router);
        Handlers\DeleteHandler::route($router);
        Handlers\PaginationHandler::route($router);
        Handlers\DetailHandler::route($router);
    }
}
