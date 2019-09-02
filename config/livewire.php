<?php

$namespace = (\Application::getNameSpace() ? \Application::getNameSpace() : 'App');

return [

    /*
    |--------------------------------------------------------------------------
    | Livewire Root Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace of your application for the purpose
    | of creating new Livewire Components via `php artisan make:livewire`
    | commands. It will detect the application's namespace by default.
    |
    */

    'namespace' => env('APP_NAMESPACE', $namespace),

    /*
    |--------------------------------------------------------------------------
    | Livewire View Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path to new Livewire component views when creating
    | them via `php artisan make:livewire` commands.
    |
    */

    'view-path' => env('LIVEWIRE_VIEW_PATH', 'livewire'),

    /*
    |--------------------------------------------------------------------------
    | Livewire Base URL
    |--------------------------------------------------------------------------
    |
    | This value sets the path to Livewire JavaScript assets, for cases where
    | your app's domain root is not the correct path. By default, Livewire
    | will load its JavaScript assets from the app's "relative root".
    |
    */

    'base_url'  => env('LIVEWIRE_BASE_URL', '/'),
];
/*  */
