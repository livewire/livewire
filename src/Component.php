<?php

namespace Livewire;

abstract class Component extends \Synthetic\Component
{
    protected $__id;

    public function setId($id)
    {
        $this->__id = $id;
    }

    public function getId()
    {
        return $this->__id;
    }

    // [key => ['id' => id, 'tag' => tag]
    public $__children = [];

    public function getChildren() { return $this->__children; }

    public function setChildren($children) { $this->__children = $children; }

    public function setChild($key, $tag, $id) { $this->__children[$key] = [$tag, $id]; }

    public function hasChild($key)
    {
        return in_array($key, array_keys($this->__children));
    }

    public function getChild($key)
    {
        return $this->__children[$key];
    }
}
