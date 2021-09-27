<?php

namespace Livewire\Hydration;

use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutImplementingWireableException;
use Livewire\Wireable;

class PublicPropertyManager
{
    public $properties;

    public function register($class, $resolver)
    {
        if (! $resolver instanceof Wireable) {
            throw new CannotRegisterPublicPropertyWithoutImplementingWireableException(get_class($class));
        }

        $this->properties[$class] = $resolver;

        return $this;
    }
}
