<?php

namespace Livewire\Connection;

use Livewire\Exceptions\ComponentMismatchException;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        return $instance->getAllPublicPropertiesDefinedBySubClass();
    }

    public static function hydrate($component, $id, $properties, $checksum)
    {
        throw_unless(md5($component.$id) === $checksum, ComponentMismatchException::class);

        $class = app('livewire')->getComponentClass($component);

        return tap(new $class($id), function ($unHydratedInstance) use ($properties) {
            foreach ($properties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }
        });
    }
}
