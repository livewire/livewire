<?php

namespace Livewire\Hydration;

use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutImplementingWireableException;
use Livewire\Wireable;
use phpDocumentor\Reflection\Types\Object_;
use Tests\Unit\CustomPublicClass;

class PublicPropertyManager
{
    private $properties = [];

    public function register($class, $resolver)
    {
        if (! new $resolver instanceof Wireable) {
            throw new CannotRegisterPublicPropertyWithoutImplementingWireableException();
        }

        $this->properties[$class] = $resolver;

        return $this;
    }

    public function properties()
    {
        return $this->properties;
    }

    public function contains($value)
    {
        foreach ($this->properties as $class => $resolver) {
            if ($value instanceof $class) {
                return true;
            }
        }

        return false;
    }

    public function get($class)
    {
        if (! $this->has($class)) {
            // Should we throw a error?
        }

        return $this->properties[$class];
    }
}
