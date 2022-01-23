<?php

namespace Livewire\Types;

use Livewire\LivewirePropertyType;
use Livewire\ReflectionPropertyType;
use ReflectionClass;

class WireableType implements LivewirePropertyType
{
    public function hydrate($instance, $request, $name, $value)
    {
        if (! $type = ReflectionPropertyType::get($instance, $name)) {
            return $value;
        }

        return ($type->getName())::fromLivewire($value);
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        return $value ? $value->toLivewire() : $value;
    }
}
