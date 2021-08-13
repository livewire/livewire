<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;

class Placeholder
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.hydrate', function ($component, $request) {
            //
        });

        Livewire::listen('component.dehydrate', function ($component, $response) {
            //
        });
    }
}
