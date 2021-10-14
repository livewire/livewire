<?php

namespace Livewire\Features;

use Livewire\Livewire;

class SupportStacks
{
    public $forStack = [];

    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.dehydrate', function ($component, $response) {
            $this->forStack = array_merge($this->forStack, $component->getForStack());
        });

        Livewire::listen('component.dehydrate.subsequent', function ($component, $response) {
            if (count($this->forStack)) {
                $response->effects['forStack'] = $this->forStack;
            }
        });

        Livewire::listen('flush-state', function() {
            $this->forStack = [];
        });
    }
}
