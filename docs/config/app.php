<?php

class ForSomeReasonGitDownNeedsThisToWorkServiceProvider extends Illuminate\Support\ServiceProvider
{
    public function register() { app('view'); }
}

return [
    'name' => 'Livewire Documentation',
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'providers' => [
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        // Non-Laravel Service Providers.
        ForSomeReasonGitDownNeedsThisToWorkServiceProvider::class,
        GitDown\GitDownServiceProvider::class,
    ],
];
