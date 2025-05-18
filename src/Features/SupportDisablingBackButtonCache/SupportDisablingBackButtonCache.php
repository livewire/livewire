<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportDisablingBackButtonCache extends ComponentHook
{
    public static $disableBackButtonCache = false;

    public static function provide()
    {
        on('flush-state', function () {
            static::$disableBackButtonCache = false;
        });

        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

        if ($kernel->hasMiddleware(DisableBackButtonCacheMiddleware::class)) {
            return;
        }

        $kernel->pushMiddleware(DisableBackButtonCacheMiddleware::class);
    }

    public function boot()
    {
        static::$disableBackButtonCache = true;
    }
}
