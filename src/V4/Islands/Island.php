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

        // @todo: this is a hack to get the component instance into the view so nested components render due to the `if (isset(\$_instance))` check in the island Blade directive...
        \Livewire\Drawer\Utils::shareWithViews('__livewire', $this->component);

        ray('island', $this->view, $this->data);

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
        $mode = $this->mode;

        // This first render happens, but on the next render it will be skipped...
        if ($mode === 'once') {
            $mode = 'skip';
        }

        return [
            'name' => $this->name,
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