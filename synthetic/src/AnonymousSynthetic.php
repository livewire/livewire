<?php

namespace Synthetic;

class AnonymousSynthetic extends Component
{
    public $properties;
    public $methods;

    public function __construct($properties, $methods)
    {
        $this->properties = $properties;
        $this->methods = $methods;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function setMethods($methods)
    {
        return $this->methods = $methods;
    }

    public function __get($property)
    {
        return $this->properties[$property];
    }

    public function __set($property, $value)
    {
        return $this->properties[$property] = $value;
    }

    public function __call($method, $params)
    {
        return $this->methods[$method](...$params);
    }
}
