<?php

namespace Livewire\HydrationMiddleware;

class IncludeIdAsRootTagAttribute implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        if (! $response->dom) return;

        $response->dom = (new AddAttributesToRootTagOfHtml)($response->dom, [
            'id' => $instance->id,
        ], $instance);
    }
}
