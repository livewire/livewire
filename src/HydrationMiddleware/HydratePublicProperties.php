<?php

namespace Livewire\HydrationMiddleware;

class HydratePublicProperties implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        // Grab the public properties out of the request.
        $publicProperties = $request['data'];

        foreach ($publicProperties as $property => $value) {
            $unHydratedInstance->setPropertyValue($property, $value);
        }
    }

    public static function dehydrate($instance, $response)
    {
        $response->data = $instance->getPublicPropertiesDefinedBySubClass();
    }
}
