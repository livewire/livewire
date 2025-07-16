<?php

namespace Livewire\V4\Islands;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Jsonable;
use Livewire\Component;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class Island implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
        public string $key,
        public string $view,
        public ?Component $component = null,
        public string $mode = 'replace',
    ) {}

    public function render()
    {
        app(ExtendBlade::class)->startLivewireRendering($this->component);

        $componentData = Utils::getPublicPropertiesDefinedOnSubclass($this->component);

        // We need to ensure that the component instance is available in the island view, so any nested islands can access it...
        $output = view($this->view, array_merge($componentData, ['__livewire' => $this->component]))->render();

        app(ExtendBlade::class)->endLivewireRendering();

        return "<!--[if ISLAND:{$this->name}:{$this->key}:{$this->mode}]><![endif]-->"
            . $output
            . "<!--[if ENDISLAND:{$this->name}:{$this->key}]><![endif]-->";
    }

    public function prepend()
    {
        $this->mode = 'prepend';

        return $this;
    }

    public function append()
    {
        $this->mode = 'append';

        return $this;
    }

    public function toJson($options = 0)
    {
        $mode = $this->mode;

        // This first render happens, but on the next render it will be skipped...
        if ($mode === 'once') {
            $mode = 'skip';
        }

        return [
            'name' => $this->name,
            'key' => $this->key,
            'mode' => $mode,
        ];
    }

    public function __toString()
    {
        return $this->render();
    }

    public function toHtml()
    {
        return $this->render();
    }
}