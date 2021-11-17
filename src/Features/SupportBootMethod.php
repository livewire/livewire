<?php

namespace Livewire\Features;

use Illuminate\Support\Facades\App;
use Livewire\ImplicitlyBoundMethod;
use Livewire\Livewire;

class SupportBootMethod
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.boot', function ($component) {
            $component->bootIfNotBooted();
        });

        Livewire::listen('component.booted', function ($component, $request) {
            if (method_exists($component, $method = 'booted')) {
                ImplicitlyBoundMethod::call(app(), [$component, $method], [$request]); 
            }
        });
    }
}
