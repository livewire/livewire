<?php

namespace Livewire\Connection;

use Illuminate\Support\Facades\Hash;
use Livewire\Exceptions\ComponentMismatchException;

class ComponentHydrator
{
    public static function dehydrate($instance)
    {
        if ($protectedOrPrivateProperties = $instance->getAllProtectedOrPrivatePropertiesDefinedBySubClass()) {
            session()->put($instance->id.'protected_properties', $protectedOrPrivateProperties);
        }

        return $instance->getAllPublicPropertiesDefinedBySubClass();
    }

    public static function hydrate($component, $id, $publicProperties, $checksum)
    {
        throw_unless(md5($component.$id) === $checksum, ComponentMismatchException::class);

        $class = app('livewire')->getComponentClass($component);

        $protectedOrPrivateProperties = session()->get($id.'protected_properties', []);

        return tap(new $class($id), function ($unHydratedInstance) use ($publicProperties, $protectedOrPrivateProperties) {
            foreach ($publicProperties as $property => $value) {
                $unHydratedInstance->setPropertyValue($property, $value);
            }

            foreach ($protectedOrPrivateProperties as $property => $value) {
                $unHydratedInstance->setProtectedPropertyValue($property, $value);
            }
        });
    }
}
