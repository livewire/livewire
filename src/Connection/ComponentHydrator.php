<?php

namespace Livewire\Connection;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        return $instance->getAllPublicPropertiesDefinedBySubClass();
    }

    public static function hydrate($class, $properties)
    {
        return tap(new $class, function ($unHydratedInstance) use ($properties) {
            foreach ($properties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }
        });
    }
}
