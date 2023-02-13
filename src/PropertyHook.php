<?php

namespace Livewire;

abstract class PropertyHook
{
    protected $component;
    private $propertyName;

    function setComponent($component)
    {
        $this->component = $component;
    }

    function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    function getValue()
    {
        return $this->component->all()[$this->propertyName];
    }

    function setValue($value)
    {
        data_set($this->component, $this->propertyName, $value);
    }

    function getName()
    {
        return $this->propertyName;
    }

    function hasAttribute($attribute)
    {
        return \Livewire\Drawer\Utils::hasAttribute($this->component, $this->propertyName, $attribute);
    }

    function storeSet($key, $value)
    {
        store($this->component)->set($key, $value);
    }

    function storeGet($key)
    {
        return store($this->component)->get($key);
    }

    function storeHas($key)
    {
        return store($this->component)->has($key);
    }
}
