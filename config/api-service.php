<?php

use Rupadana\ApiService\Policies\TokenPolicy;

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
            'policy_class' => TokenPolicy::class
        ],
    ],
];
