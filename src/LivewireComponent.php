<?php

namespace Livewire;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

abstract class LivewireComponent
{
    use Concerns\CanBeSerialized,
        Concerns\ValidatesInput;

    public $id;
    public $prefix;
    public $redirectTo;
    public $callOnParent;
    // This gets used a way to track data between requests by the wrapper.
    public $children = [];
    public $listenersByChildComponentId = [];

    public function __construct($id, $prefix)
    {
        $this->id = $id;
        $this->prefix = $prefix;
    }

    public function redirectTo($url)
    {
        $this->redirectTo = $url;
    }

    public function callOnParent($method, ...$params)
    {
        $this->callOnParent = ['method' => $method, 'params' => $params];
    }

    public function getPropertyValue($prop) {
        // This is used by wrappers. Otherwise,
        // users would have to declare props as "public".
        return $this->{$prop};
    }

    public function hasProperty($prop) {
        return property_exists($this, $prop);
    }

    public function setPropertyValue($prop, $value) {
        return $this->{$prop} = $value;
    }
}
