<?php

namespace Livewire\Connection;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        return base64_encode(serialize([
            'id' => $instance->id,
            'class' => get_class($instance),
            'properties' => $instance->getAllPropertiesDefinedBySubClass(),
        ]));
    }

    public static function hydrate($serialized)
    {
        list($id, $class, $properties) = array_values(unserialize(base64_decode($serialized)));

        return tap(new $class($id), function ($unHydratedInstance) use ($properties) {
            foreach ($properties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }
        });
    }
}
