<?php

return [
    'default' => 'file',
    'prefix' => 'laravel_cache',
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => base_path('cache'),
        ],
    ],
];
