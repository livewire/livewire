<?php

namespace Livewire\Features\SupportEvents;

use Livewire\PropertyHook;

#[\Attribute]
class Listener extends PropertyHook
{
    public function __construct(public $event) {}

    public function boot()
    {
        $this->component->__propertyAnnotations[$this->event] = $this->getName();
    }
}
