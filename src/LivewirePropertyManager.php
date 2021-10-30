<?php

namespace Livewire;

use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutExtendingThePropertyHandlerException;

class LivewirePropertyManager
{
    private $properties = [];

    public function register($class, $resolver)
    {
        throw_unless(is_subclass_of($resolver, PropertyHandler::class), new CannotRegisterPublicPropertyWithoutExtendingThePropertyHandlerException());

        $this->properties[$class] = $resolver;

        return $this;
    }

    public function properties()
    {
        return $this->properties;
    }

    public function has($value)
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
        if (! $this->has($class)) {
            return null;
        }

        $resolver = $this->properties[get_class($class)];

        return new $resolver($class);
    }

    public function getResolver($class)
    {
        if (! $this->has($class)) {
            return null;
        }

        return $this->properties[$class];
    }
}
