<?php

namespace Livewire\V4\Islands;

use Livewire\Component;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class DefaultIsland
{
    public function __construct(
        public string $key,
        public string $view,
        public ?Component $component = null,
    ) {}

    public function render()
    {
        app(ExtendBlade::class)->startLivewireRendering($this->component);

        $componentData = Utils::getPublicPropertiesDefinedOnSubclass($this->component);

        // We need to ensure that the component instance is available in the island view, so any nested islands can access it...
        $output = view($this->view, array_merge($componentData, ['__livewire' => $this->component]))->render();

        app(ExtendBlade::class)->endLivewireRendering();

        return "<!--[if ISLAND:{$this->key}]><![endif]-->"
            . $output
            . "<!--[if ENDISLAND:{$this->key}]><![endif]-->";
    }
}