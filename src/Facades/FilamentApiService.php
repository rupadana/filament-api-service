<?php

namespace Rupadana\FilamentApiService\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rupadana\FilamentApiService\FilamentApiService
 */
class FilamentApiService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Rupadana\FilamentApiService\FilamentApiService::class;
    }
}
