<?php

namespace Livewire\HydrationMiddleware;

class PrioritizeDataUpdatesBeforeActionCalls implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        // Put all the "syncInput" actions first.
        $request->updates = (array) $request->updates;

        usort($request->updates, function ($a, $b) {
            return $a['type'] !== 'syncInput' && $b['type'] === 'syncInput'
                ? 1 : 0;
        });

        $request->updates = (object) $request->updates;
    }

    public static function dehydrate($instance, $response)
    {
        //
    }
}
