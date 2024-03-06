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
    'route' => [
        'wrap_with_panel_id' => true,
    ],
];
