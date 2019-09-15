<?php

namespace Livewire\Connection;

use Livewire\ComponentCacheManager;
use Livewire\ComponentChecksumManager;
use Livewire\Exceptions\CorruptComponentPayloadException;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        // Store the protected properties in the cache.
        if ($protectedOrPrivateProperties = $instance->getProtectedOrPrivatePropertiesDefinedBySubClass()) {
            (new ComponentCacheManager($instance))->put(
                '__protected_properties',
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
            new CorruptComponentPayloadException($component)
        );

        $class = app('livewire')->getComponentClass($component);

        $unHydratedInstance = new $class($id);

        // Grab the protected properties out of the cache.
        $protectedOrPrivateProperties = (new ComponentCacheManager($unHydratedInstance))
            ->get('__protected_properties', []);

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
