<?php

namespace Livewire\HydrationMiddleware;

class PerformPublicPropertyFromDataBindingUpdates implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        foreach ($request['actionQueue'] as $action) {
            if ($action['type'] !== 'syncInput') return;

            $data = $action['payload'];

            $unHydratedInstance->updating($data['name'], $data['value']);
            $unHydratedInstance->syncInput($data['name'], $data['value']);
            $unHydratedInstance->updated($data['name'], $data['value']);
        }
    }

    public static function dehydrate($instance, $response)
    {
        //
    }
}
