<?php

namespace Livewire\Hydration;

use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutImplementingWireableException;
use Livewire\Wireable;

class PublicPropertyManager
{
    public $properties;

    public function register($class)
    {
        if (! $class instanceof Wireable) {
            throw new CannotRegisterPublicPropertyWithoutImplementingWireableException(get_class($class));
        }

        $this->properties[] = $class;

        return $this;
    }
}
