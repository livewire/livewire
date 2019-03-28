<?php

namespace Livewire\Connection;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        return base64_encode(serialize([
            'class' => get_class($instance),
            'id' => $instance->id,
            'prefix' => $instance->prefix,
            'properties' => $instance->getAllPropertiesDefinedBySubClass(),
        ]));
    }

    public static function hydrate($serialized)
    {
        list($class, $id, $prefix, $properties) = array_values(unserialize(base64_decode($serialized)));

        return tap(new $class($id, $prefix), function ($unHydratedInstance) use ($properties) {
            foreach ($properties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }
        });
    }
}
