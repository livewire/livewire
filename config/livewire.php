<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace for Livewire component classes in
    | your application. This value effects component auto-discovery and
    | any livewire file helper commands, like `artisan make:livewire`.
    |
    | After changing this item, run: `php artisan livewire:discover`
    |
    */

    'class_namespace' => 'App\\Http\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path for Livewire component views. This effects
    | File manipulation helper commands like `artisan make:livewire`
    |
    */

    'view_path' => resource_path('views/livewire'),

    /*
    |--------------------------------------------------------------------------
    | Livewire Assets URL
    |--------------------------------------------------------------------------
    |
    | This value sets the path to Livewire JavaScript assets, for cases where
    | your app's domain root is not the correct path. By default, Livewire
    | will load its JavaScript assets from the app's "relative root".
    |
    | Examples: "/assets", "myurl.com/app"
    |
    */

    'asset_url'  => null,

    /*
    |--------------------------------------------------------------------------
    | Livewire Endpoint Middleware Group
    |--------------------------------------------------------------------------
    |
    | This value sets the middleware group that will be applied to the main
    | Livewire "message" endpoint (the endpoint that gets hit everytime,
    | a Livewire component updates). It is set to "web" by default.
    |
    */

    'middleware_group'  => 'web',

    /*
    |--------------------------------------------------------------------------
    | Manifest File Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path to Livewire manifest file path.
    | The default should work for most cases (which is
    | "<app_root>/bootstrap/cache/livewire-components.php)", but for specific
    | cases like when hosting on Laravel Vapor, it could be set to a different value.
    |
    | Example: For Laravel Vapor, it would be "/tmp/storage/bootstrap/cache/livewire-components.php"
    |
    */

    'manifest_path' => null,

];
