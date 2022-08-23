<?php

namespace Livewire\Mechanisms;

use Livewire\Drawer\IsSingleton;

class ComponentRegistry
{
    use IsSingleton;

    protected $aliases = [];

    function get($name)
    {
        $subject = $name;

        if (isset($this->aliases[$name])) {
            $subject = $this->aliases[$name];
        }

        if (is_object($subject)) return clone $subject;

        if (! class_exists((string) str($subject)->studly())) throw new \Exception('Not a class');

        return new (str($subject)->studly()->toString());
    }

    function register($name, $class = null)
    {
        if (is_null($class)) {
            $class = $name;
            $name = $class::getName();
        }

        $this->aliases[$name] = $class;
    }
}
