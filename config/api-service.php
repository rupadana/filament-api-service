<?php

// config for Rupadana/ApiService
return [
    'navigation' => [
        'group' => [
            'token' => 'User',
        ],
    ],
    'models' => [
        'token' => [
            'enable_policy' => true,
        ],
    ],
    'tenancy' => [
        'enabled'   => false,
        'is_tenant_aware' => false,
        'tenant_ownership_relationship_name' => 'team',
    ]
];
