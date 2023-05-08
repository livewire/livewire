<?php

namespace Livewire\Features\SupportProps;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class Prop extends LivewireAttribute
{
    public function mount($params)
    {
        $property = $this->getName();

        if (! array_key_exists($property, $params)) return;

        $this->setValue($params[$property]);
    }
}
