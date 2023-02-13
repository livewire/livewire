<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\ComponentHook;

class SupportLockedProperties extends ComponentHook
{
    public function update($propertyName)
    {
        if (! $this->hasAttribute($propertyName, Locked::class)) return;

        throw new \Exception('Cannot update locked property: ['.$propertyName.']');
    }
}
