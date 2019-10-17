<?php

namespace Livewire;

use ReflectionClass;

class ObjectPrybar
{
    protected $obj;

    public function __construct($obj)
    {
        $this->obj = $obj;
        $this->reflected = new ReflectionClass($obj);
    }

    public function getProperty($name)
    {
        $property = $this->reflected->getProperty($name);

        $property->setAccessible(true);

        return $property->getValue($this->obj);
    }

    public function setProperty($name, $value)
    {
        $property = $this->reflected->getProperty($name);

        $property->setAccessible(true);

        $property->setValue($this->obj, $value);
    }
}
