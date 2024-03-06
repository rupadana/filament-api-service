<?php

return [
    'navigation' => [
        /**
         * @deprecated 3.2
         */
        'group' => [
            'token' => 'User',
        ],
        'token' => [
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
        'wrap_with_panel_id' => true,
    ],
];
