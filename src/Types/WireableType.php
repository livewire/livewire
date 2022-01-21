<?php

namespace Livewire\Types;

use Livewire\LivewirePropertyType;
use ReflectionClass;

class WireableType implements LivewirePropertyType
{
    public function hydrate($instance, $name, $value)
    {
        $type = (new ReflectionClass($instance))
            ->getProperty($name)
            ->getType()
            ->getName();

        return $type::fromLivewire($value);
    }

    public function dehydrate($instance, $name, $value)
    {
        return $value->toLivewire();
    }
}
