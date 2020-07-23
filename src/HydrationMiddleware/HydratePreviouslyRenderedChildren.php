<?php

namespace Livewire\HydrationMiddleware;

class HydratePreviouslyRenderedChildren implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $unHydratedInstance->setPreviouslyRenderedChildren($request->memo['children']);
    }

    public static function dehydrate($instance, $response)
    {
        $response->memo['children'] = $instance->getRenderedChildren();
    }
}
