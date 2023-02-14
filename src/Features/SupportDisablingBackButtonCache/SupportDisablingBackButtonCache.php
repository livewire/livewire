<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Livewire\Mechanisms\DataStore;
use Livewire\ComponentHook;

class SupportDisablingBackButtonCache extends ComponentHook
{
    public static $disableBackButtonCache = false;

    static function provide()
    {
        app('events')->listen(RequestHandled::class, function ($handled) {
            if (static::$disableBackButtonCache) {
                $handled->response->headers->add([
                    "Pragma" => "no-cache",
                    "Expires" => "Fri, 01 Jan 1990 00:00:00 GMT",
                    "Cache-Control" => "no-cache, must-revalidate, no-store, max-age=0, private",
                ]);
            }
        });
    }
}
