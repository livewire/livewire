<?php

namespace Livewire\Features;

use Livewire\Livewire;

class SupportStacks
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.dehydrate.subsequent', function ($component, $response) {
            if ($pushes = $component->getPushesAndAppends()) {
                dd($pushes);
                $response->effects['pushes'] = $pushes;
            }
        });
    }
}
