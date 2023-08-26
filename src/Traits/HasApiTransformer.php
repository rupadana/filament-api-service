<?php
namespace Rupadana\FilamentApiService\Traits;

use Rupadana\FilamentApiService\Transformers\DefaultTransformer;
trait HasApiTransformer {

    public static string | null $transformer = DefaultTransformer::class;

    public static function getApiTransformer() {
        return static::$transformer;
    }
}