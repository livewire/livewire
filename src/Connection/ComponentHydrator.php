<?php

namespace Livewire\Connection;

use Livewire\ComponentCacheManager;
use Livewire\ComponentChecksumManager;
use Livewire\Exceptions\CorruptComponentPayloadException;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        $instance->getProtectedStorageEngine()->saveProtectedData($instance);

        return $instance->getPublicPropertiesDefinedBySubClass();
    }

    public static function hydrate($component, $id, $publicProperties, $checksum, $protected = null)
    {
        // Make sure the data coming back to hydrate a component hasn't been tamered with.
        $checksumManager = new ComponentChecksumManager;
        throw_unless(
            $checksumManager->check($checksum, $component, $id, $publicProperties),
            new CorruptComponentPayloadException($component)
        );

        $class = app('livewire')->getComponentClass($component);

        $unHydratedInstance = new $class($id);

        return tap($unHydratedInstance, function ($unHydratedInstance) use ($publicProperties, $protected) {
            foreach ($publicProperties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }

            $unHydratedInstance->getProtectedStorageEngine()->restoreProtectedData($unHydratedInstance, $protected);
        });
    }
}
