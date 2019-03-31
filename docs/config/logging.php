<?php

return [
    'default' => 'single',
    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => base_path('cache/laravel.log'),
            'level' => 'debug',
        ],
    ],
];
