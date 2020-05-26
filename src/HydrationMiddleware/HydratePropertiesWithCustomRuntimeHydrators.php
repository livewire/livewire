<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Livewire;

class HydratePropertiesWithCustomRuntimeHydrators implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $publicProperties = $unHydratedInstance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            $newValue = Livewire::performHydrateProperty($value, $property, $unHydratedInstance);

            if ($newValue !== $value) {
                $unHydratedInstance->{$property} = $newValue;
            }
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicProperties = $instance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            $newValue = Livewire::performDehydrateProperty($value, $property, $instance);

            if ($newValue !== $value) {
                $instance->{$property} = $newValue;
            }
        }
    }
}
