<?php

namespace Livewire\Connection;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        return json_encode([
            'id' => $instance->id,
            'class' => get_class($instance),
            'properties' => $instance->getAllPublicPropertiesDefinedBySubClass(),
        ]);
    }

    public static function hydrate($serialized)
    {
        list($id, $class, $properties) = array_values(json_decode($serialized, true));

        return tap(new $class($id), function ($unHydratedInstance) use ($properties) {
            foreach ($properties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }
        });
    }
}
