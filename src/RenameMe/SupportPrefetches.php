<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;

class SupportPrefetches
{
    public static $prefetchCacheStack = [];

    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.hydrate.subsequent', function ($component, $request) {
            array_push(static::$prefetchCacheStack, $request->memo['prefetch']);
        });

        Livewire::listen('component.dehydrate', function ($component, $response) {
            $response->memo['prefetch'] = array_pop(static::$prefetchCacheStack);
        });
    }
}
