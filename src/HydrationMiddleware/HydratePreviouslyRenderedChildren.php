<?php

namespace Livewire\HydrationMiddleware;

class HydratePreviouslyRenderedChildren implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $unHydratedInstance->setPreviouslyRenderedChildren($request['children']);
    }

    public static function dehydrate($instance, $response)
    {
        $response->children = $instance->getRenderedChildren();
    }
}
