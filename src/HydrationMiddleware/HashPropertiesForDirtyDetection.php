<?php

namespace Livewire\HydrationMiddleware;

class HashPropertiesForDirtyDetection implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $unHydratedInstance->hashPropertiesForDirtyDetection();
    }

    public static function dehydrate($instance, $response)
    {
        if ($dirty = $instance->getDirtyProperties()) {
            $response->effects['dirty'] = $dirty;
        }
    }
}
