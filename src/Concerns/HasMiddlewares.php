<?php

namespace Rupadana\ApiService\Concerns;

trait HasMiddlewares
{
    public static function useResourceMiddlewares(): bool
    {
        return config('api-service.route.use_resource_middlewares', false);
    }

    public static function getHandlerMiddlewares(): array
    {
        return config('api-service.route.middlewares', []);
    }
}
