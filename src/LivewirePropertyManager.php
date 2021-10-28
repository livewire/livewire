<?php

namespace Livewire;

use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutImplementingWireableException;

class LivewirePropertyManager
{
    private $properties = [];

    public function register($class, $resolver)
    {
//        if (! new $resolver instanceof Wireable) {
//            throw new CannotRegisterPublicPropertyWithoutImplementingWireableException();
//        }

        $this->properties[$class] = $resolver;

        return $this;
    }

    public function properties()
    {
        return $this->properties;
    }

    public function contains($value)
    {
        $value = (new \ReflectionClass($value))->getName();

        foreach ($this->properties as $class => $resolver) {
            if ($value === $class) {
                return true;
            }
        }

        return false;
    }

    public function get($class)
    {
        if (! $this->contains($class)) {
            return null;
        }

        $resolver = $this->properties[get_class($class)];

        return new $resolver($class);
    }

    public function getResolver($class)
    {
        if (! $this->contains($class)) {
            return null;
        }

        return $this->properties[$class];
    }
}
