<?php

namespace Livewire\HydrationMiddleware;

class HydratePreviouslyRenderedChildren implements HydrationMiddleware
{
    public function hydrate($unHydratedInstance, $request)
    {
        $unHydratedInstance->setPreviouslyRenderedChildren($request['children']);
    }

    public function dehydrate($instance, $response)
    {
        $response->children = $instance->getRenderedChildren();
    }
}
