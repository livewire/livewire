<?php

namespace Livewire\Connection;

use Livewire\ComponentSessionManager;
use Livewire\ComponentChecksumManager;
use Livewire\Exceptions\ComponentMismatchException;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        // Store the protected properties in the session.
        if ($protectedOrPrivateProperties = $instance->getProtectedOrPrivatePropertiesDefinedBySubClass()) {
            (new ComponentSessionManager($instance))->put(
                'protected_properties',
                $protectedOrPrivateProperties
            );
        }

        return $instance->getPublicPropertiesDefinedBySubClass();
    }

    public static function hydrate($component, $id, $publicProperties, $checksum)
    {
        // Make sure the data coming back to hydrate a component hasn't been tamered with.
        $checksumManager = new ComponentChecksumManager;
        throw_unless(
            $checksumManager->check($checksum, $component, $id, $publicProperties),
            ComponentMismatchException::class
        );

        $class = app('livewire')->getComponentClass($component);

        $unHydratedInstance = new $class($id);

        // Grab the protected properties out of the session.
        $protectedOrPrivateProperties = (new ComponentSessionManager($unHydratedInstance))
            ->get('protected_properties', []);

        return tap($unHydratedInstance, function ($unHydratedInstance) use ($publicProperties, $protectedOrPrivateProperties) {
            foreach ($publicProperties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }

            foreach ($protectedOrPrivateProperties as $property => $value) {
                $unHydratedInstance->setProtectedPropertyValue($property, $value);
            }
        });
    }
}
