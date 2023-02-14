<?php

namespace Livewire\Mechanisms\NestingComponents;

trait HandlesNestingComponents
{
    public $__children = [];
    public $__previous_children = [];

    function getChildren() { return $this->__children; }

    function setChildren($children) { $this->__children = $children; }

    function setPreviouslyRenderedChildren($children) { $this->__previous_children = $children; }
    function getPreviouslyRenderedChildren() { return $this->__previous_children; }

    function setChild($key, $tag, $id) { $this->__children[$key] = [$tag, $id]; }

    function hasPreviouslyRenderedChild($key) {
        return in_array($key, array_keys($this->__previous_children));
    }

    function hasChild($key)
    {
        return in_array($key, array_keys($this->__children));
    }

    function getChild($key)
    {
        return $this->__children[$key];
    }

    function getPreviouslyRenderedChild($key)
    {
        return $this->__previous_children[$key];
    }

    function keepRenderedChildren()
    {
        $this->setChildren($this->getPreviouslyRenderedChildren());
    }
}
