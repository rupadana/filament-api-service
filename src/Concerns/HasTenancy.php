<?php

namespace Rupadana\ApiService\Concerns;

trait HasTenancy
{
    public static function isTenancyEnabled(): bool
    {
        return config('api-service.tenancy.enabled', false);
    }

    public static function tenancyAwareness(): bool
    {
        return config('api-service.tenancy.awareness', false);
    }
}
