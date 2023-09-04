<?php

namespace Rupadana\ApiService\Http;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Rupadana\ApiService\Traits\HttpResponse;
use Rupadana\ApiService\Transformers\DefaultTransformer;

class Handlers
{
    use HttpResponse;

    public static string | null $uri = '/';
    public static string $method = 'get';
    public static string | null $resource = null;
    protected static string $keyName = 'id';

    const POST = 'post';
    const GET = 'get';
    const DELETE = 'delete';
    const PATCH = 'patch';
    const PUT = 'put';

    public static function getMethod() {
        return static::$method;
    }

    public static function route(Router $router)
    {
        $method = static::getMethod();

        $router->$method(static::$uri, [static::class, 'handler']);
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    public static function getApiTransformer():? string
    {
        if (!method_exists(static::$resource, 'getApiTransformer')) {
            return DefaultTransformer::class;
        }

        return static::$resource::getApiTransformer();
    }

    public static function getKeyName():? string {
        return static::$keyName;
    }
}
