<?php

return [
    'name' => 'Livewire Documentation',
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'key' => env('APP_KEY'),
    'providers' => [
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        // Non-Laravel Service Providers.
        AppServiceProvider::class,
        GitDown\GitDownServiceProvider::class,
    ],
];

class AppServiceProvider extends Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        // For some silly reason, this line needs to be here for the
        // @gitdown blade directive to register.
        // This line effectively makes the "blade.compiler" available
        // to the rest of the application.
        app('view');
    }
}
