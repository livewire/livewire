<?php

namespace Livewire\Features;

use Livewire\Livewire;
use Livewire\ImplicitlyBoundMethod;

class SupportRootElementTracking
{
    static function init() { return new static; }

    protected $componentIdMethodMap = [];

    function __construct()
    {
        Livewire::listen('component.dehydrate.initial', function ($component, $response) {
            if (! $html = data_get($response, 'effects.html')) return

            data_set($response, 'effects.html', $this->addComponentEndingMarker($html, $component));
        });
    }

    public function addComponentEndingMarker($html, $component)
    {
        return $html."\n<!-- Livewire Component wire-end:".$component->id.' -->';
    }
}
