<?php

declare(strict_types=1);

return [
    'navigation' => [
        'token' => [
            'cluster' => null,
            'group' => 'User',
            'sort' => -1,
            'icon' => 'heroicon-o-key',
        ],
    ],
    'models' => [
        'token' => [
            'enable_policy' => true,
        ],
    ],
    'route' => [
        'panel_prefix' => true,
        'use_resource_middlewares' => false,
        'default_transformer_name' => 'default',
        'api_version_method' => 'path', // options: ['path', 'query', 'headers']
        'api_version_parameter_name' => env('API_VERSION_PARAMETER_NAME', 'version'),
        'api_transformer_header' => env('API_TRANSFORMER_HEADER', 'X-API-TRANSFORMER'),
    ],
    'tenancy' => [
        'enabled' => false,
        'awareness' => false,
    ]
];
