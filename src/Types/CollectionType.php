<?php

namespace Livewire\Types;

use Livewire\LivewirePropertyType;

class CollectionType implements LivewirePropertyType
{
    public function hydrate($instance, $request, $name, $value)
    {
        return collect($value);
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        return $value->toArray();
    }
}
