<?php

namespace Rupadana\ApiService\Concerns;

trait HasApiQuery
{
    public static array $allowedFields = [];
    public static array $allowedSorts = [];
    public static array $allowedFilters = [];
    public static array $allowedIncludes = [];

    public static function getAllowedFields(): array
    {
        return static::$allowedFields;
    }

    public static function getAllowedSorts(): array
    {
        return static::$allowedSorts;
    }

    public static function getAllowedFilters(): array
    {
        return static::$allowedFilters;
    }

    public static function getAllowedIncludes(): array
    {
        return static::$allowedIncludes;
    }
}
