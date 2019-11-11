<?php

namespace Livewire\HydrationMiddleware;

class PrioritizeDataUpdatesBeforeActionCalls implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        // Put all the "syncInput" actions first.
        usort($request['actionQueue'], function ($a, $b) {
            return $a['type'] !== 'syncInput' && $b['type'] === 'syncInput'
                ? 1 : 0;
        });
    }

    public static function dehydrate($instance, $response)
    {
        //
    }
}
