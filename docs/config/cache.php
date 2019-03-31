<?php

return [
    'default' => 'file',
    'prefix' => 'laravel_cache',
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
    ],
];
