<?php

namespace Livewire\Features;

use Livewire\Livewire;
use Illuminate\Support\Facades\App;

class SupportBootMethod
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.hydrate', function ($component, $response) {
            $component->bootIfNotBooted();
        });
    }
}
