<?php

namespace Livewire\V4\Islands;

use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use Livewire\Component;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class Island implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
        public string $view,
        public array $data = [],
        public ?Component $component = null,
        public string $mode = 'replace',
    ) {}

    public function render()
    {
        app(ExtendBlade::class)->startLivewireRendering($this->component);

        $output = view($this->view, $this->data)->render();

        app(ExtendBlade::class)->endLivewireRendering();

        return "<!--[if ISLAND:{$this->name}:{$this->mode}]><![endif]-->"
            . $output
            . "<!--[if ENDISLAND:{$this->name}]><![endif]-->";
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
        return [
            'name' => $this->name,
            'mode' => $this->mode,
            'content' => $this->render(),
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