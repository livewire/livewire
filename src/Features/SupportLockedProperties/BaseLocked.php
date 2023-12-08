<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseLocked extends LivewireAttribute
{
    public function update()
    {
        throw new CannotUpdateLockedPropertyException($this->getName());
    }
}
