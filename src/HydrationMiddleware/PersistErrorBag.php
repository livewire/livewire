<?php

namespace Livewire\HydrationMiddleware;

class PersistErrorBag implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $unHydratedInstance->setErrorBag(
            $request['errorBag'] ?? []
        );
    }

    public static function dehydrate($instance, $response)
    {
        if ($errors = $instance->getErrorBag()->toArray()) {
            $response->errorBag = $errors;
        }
    }
}
