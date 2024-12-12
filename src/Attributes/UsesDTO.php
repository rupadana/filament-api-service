<?php

namespace Rupadana\ApiService\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class UsesDTO
{
    public function __construct(public ?string $dtoClass = null) {}
}
