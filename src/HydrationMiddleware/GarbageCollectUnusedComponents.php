<?php

namespace Livewire\HydrationMiddleware;

use Livewire\ComponentCacheManager;

class GarbageCollectUnusedComponents implements HydrationMiddleware
{
    public static $gc;

    public static function hydrate($unHydratedInstance, $request)
    {
        static::$gc = $request['gc'];
    }

    public static function dehydrate($instance, $response)
    {
        $response->gc = ComponentCacheManager::garbageCollect(static::$gc);
    }
}
