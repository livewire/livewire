<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Livewire\Mechanisms\DataStore;
use Livewire\ComponentHook;

class SupportDisablingBackButtonCache extends ComponentHook
{
    public static $disableBackButtonCache = false;

    public static function provide()
    {
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
