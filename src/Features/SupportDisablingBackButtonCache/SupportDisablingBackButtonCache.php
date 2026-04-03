<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportDisablingBackButtonCache extends ComponentHook
{
    public static $disableBackButtonCache = null;

    public static function provide()
    {
        on('flush-state', function () {
            static::$disableBackButtonCache = null;
        });

        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

        if ($kernel->hasMiddleware(DisableBackButtonCacheMiddleware::class)) {
            return;
        }

        $kernel->pushMiddleware(DisableBackButtonCacheMiddleware::class);
    }

    public function boot()
    {
        if (! is_null(static::$disableBackButtonCache)) {
            return;
        }

        static::$disableBackButtonCache = ! config('livewire.back_button_cache', false);
    }
}
