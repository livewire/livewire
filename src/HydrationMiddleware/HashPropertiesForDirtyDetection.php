<?php

namespace Livewire\HydrationMiddleware;

class HashPropertiesForDirtyDetection implements HydrationMiddleware
{
    public function hydrate($unHydratedInstance, $request)
    {
        $unHydratedInstance->hashPropertiesForDirtyDetection();
    }

    public function dehydrate($instance, $response)
    {
        $response->dirtyInputs = $instance->getDirtyProperties();
    }
}
