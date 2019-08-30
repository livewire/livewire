<?php

namespace Livewire\Connection;

use Livewire\ComponentChecksumManager;
use Livewire\Exceptions\ComponentMismatchException;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        return $instance->getPublicPropertiesDefinedBySubClass();
    }

    public static function hydrate($component, $id, $properties, $checksum)
    {
        $class = app('livewire')->getComponentClass($component);

        $checksumManager = new ComponentChecksumManager;

        throw_unless($checksumManager->check($checksum, $component, $id, $properties), ComponentMismatchException::class);

        return tap(new $class($id), function ($unHydratedInstance) use ($properties) {
            foreach ($properties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }
        });
    }
}
