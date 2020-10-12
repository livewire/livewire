<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;

class SupportComponentTraits
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.hydrate', function ($component) {
            $component->initializeTraits();
        });
    }
}
