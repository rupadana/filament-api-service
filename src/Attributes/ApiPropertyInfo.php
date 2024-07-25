<?php

namespace Rupadana\ApiService\Attributes;

use Attribute;
use OpenApi\Attributes\Property;


#[\AllowDynamicProperties]
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ApiPropertyInfo extends Property
{
    public static function keyword(): string
    {
        return 'api-property-info';
    }

    public function parameters(): array
    {
        return [
            'title',
            'description',
            'example'
        ];
    }
}
