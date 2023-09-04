<?php
namespace Rupadana\ApiService\Traits;

use Rupadana\ApiService\Transformers\DefaultTransformer;
trait HasApiTransformer {

    public static string | null $transformer = DefaultTransformer::class;

    public static function getApiTransformer() {
        return static::$transformer;
    }
}