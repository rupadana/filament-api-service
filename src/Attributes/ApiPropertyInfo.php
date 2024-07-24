<?php

namespace Rupadana\ApiService\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ApiPropertyInfo
{
    public string $title;
    public string $description;
    public string $example;

    public function __construct(string $title = '', string $description = '', string $example = '')
    {
        $this->title = $title;
        $this->description = $description;
        $this->example = $example;
    }

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
