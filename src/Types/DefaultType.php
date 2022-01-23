<?php

namespace Livewire\Types;

use Livewire\LivewirePropertyType;

class DefaultType implements LivewirePropertyType
{
    public function hydrate($instance, $request, $name, $value)
    {
        return $value;
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        return $value;
    }
}
