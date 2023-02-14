<?php

namespace Livewire\Features\SupportEvents;

use Livewire\PropertyHook;

use function Livewire\store;

#[\Attribute]
class Listener extends PropertyHook
{
    public function __construct(public $event) {}

    public function boot()
    {
        store($this->component)->push('listenersFromPropertyHooks', $this->getName(), $this->event);
    }
}
