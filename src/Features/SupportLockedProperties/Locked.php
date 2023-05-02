<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\PropertyHook;

#[\Attribute]
class Locked extends PropertyHook
{
    function __construct($thing)
    {
        dd($thing);
    }

    public function update()
    {
        throw new \Exception('Cannot update locked property: ['.$this->getName().']');
    }
}
