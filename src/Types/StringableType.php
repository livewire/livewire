<?php

namespace Livewire\Types;

use Illuminate\Support\Stringable;
use Livewire\LivewirePropertyType;

class StringableType implements LivewirePropertyType
{
    public function hydrate($instance, $request, $name, $value)
    {
        return new Stringable($value);
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        return $value->__toString();
    }
}
