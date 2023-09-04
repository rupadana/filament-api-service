<?php

namespace Rupadana\ApiService\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rupadana\ApiService\ApiService
 */
class ApiService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Rupadana\ApiService\ApiService::class;
    }
}
