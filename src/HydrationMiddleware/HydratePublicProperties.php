<?php

namespace Livewire\HydrationMiddleware;

class HydratePublicProperties implements HydrationMiddleware
{
    public function hydrate($unHydratedInstance, $request)
    {
        // Grab the public properties out of the request.
        $publicProperties = $request['data'];

        foreach ($publicProperties as $property => $value) {
            $unHydratedInstance->setPropertyValue($property, $value);
        }
    }

    public function dehydrate($instance, $response)
    {
        $response->data = $instance->getPublicPropertiesDefinedBySubClass();
    }
}
