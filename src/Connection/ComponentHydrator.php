<?php

namespace Livewire\Connection;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        return [
            'id' => $instance->id,
            'class' => get_class($instance),
            'properties' => $instance->getAllPublicPropertiesDefinedBySubClass(),
        ];
    }

    public static function hydrate($serialized)
    {
        $id = $serialized['id'];
        $class = $serialized['class'];
        $properties = $serialized['properties'];

        return tap(new $class($id), function ($unHydratedInstance) use ($properties) {
            foreach ($properties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }
        });
    }
}
