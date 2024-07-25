<?php

namespace Rupadana\ApiService\Attributes;

use Attribute;

#[\AllowDynamicProperties]
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ApiPropertyInfo
{
    public string $title;
    public string $description;
    public string $example;
    private array $extraProperties;

    public function __construct(string $title = '', string $description = '', string $example = '', $extraProperties = [])
    {
        $this->title = $title;
        $this->description = $description;
        $this->example = $example;
        $this->extraProperties = $extraProperties;
    }

    public function __get($name)
    {
        return $this->extraProperties[$name] ?? null;
    }

    public function __isset($name)
    {
        return isset($this->extraProperties[$name]);
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
