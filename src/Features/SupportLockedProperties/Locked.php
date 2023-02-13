<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\PropertyHook;

#[\Attribute]
class Locked extends PropertyHook
{
    public function update()
    {
        throw new \Exception('Cannot update locked property: ['.$this->getName().']');
    }
}
