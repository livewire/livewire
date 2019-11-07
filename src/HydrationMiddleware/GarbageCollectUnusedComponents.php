<?php

namespace Livewire\HydrationMiddleware;

use Livewire\ComponentCacheManager;

class GarbageCollectUnusedComponents implements HydrationMiddleware
{
    public static $gc;

    public function hydrate($unHydratedInstance, $request)
    {
        static::$gc = $request['gc'];
    }

    public function dehydrate($instance, $response)
    {
        $response->gc = ComponentCacheManager::garbageCollect(static::$gc);
    }
}
