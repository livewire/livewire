<?php

namespace Livewire;

use Illuminate\Support\Arr;
use ReflectionClass;

abstract class ReflectionPropertyType
{
    /** @return \ReflectionNamedType|null */
    public static function get($class, $property)
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            return null;
        }

        $instance = new ReflectionClass($class);

        if (! $instance->hasProperty($property)) {
            return null;
        }

        $property = $instance->getProperty($property);

        if (! $property->hasType()) {
            return null;
        }

        $type = $property->getType();

        // Support union types in PHP 8 (just uses first in the list)
        if (method_exists($type, 'getTypes')) {
            $type = $type->getTypes();
        }

        return Arr::wrap($type)[0];
    }
}
