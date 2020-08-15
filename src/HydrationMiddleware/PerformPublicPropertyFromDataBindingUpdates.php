<?php

namespace Livewire\HydrationMiddleware;

class PerformPublicPropertyFromDataBindingUpdates implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        foreach ($request->updates as $update) {
            if ($update['type'] !== 'syncInput') return;

            $data = $update['payload'];

            $unHydratedInstance->syncInput($data['name'], $data['value']);
        }
    }

    public static function dehydrate($instance, $response)
    {
        //
    }
}
