<?php

namespace Livewire\HydrationMiddleware;

class PersistErrorBag implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $unHydratedInstance->setErrorBag(
            $request->memo['errors'] ?? []
        );
    }

    public static function dehydrate($instance, $response)
    {
        $response->memo['errors'] = $instance->getErrorBag()->toArray();
    }
}
