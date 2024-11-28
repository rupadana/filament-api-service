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
        'api_transformer_header' => env('API_TRANSFORMER_HEADER', 'X-API-TRANSFORMER'),
    ],
    'tenancy' => [
        'enabled' => false,
        'awareness' => false,
    ]
];
