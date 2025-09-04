<?php

namespace Livewire\V4\Islands;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Jsonable;
use Livewire\Component;

class Island implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
        public string $key,
        public string $view,
        public ?Component $component = null,
        public string $mode = 'replace',
        public string $render = 'once',
        public bool $defer = false,
        public bool $lazy = false,
        public ?string $poll = null,
        public ?string $placeholder = null,
    ) {}

    public function render($force = false)
    {
        if ($force) {
            return (new DefaultIsland($this->key, $this->view, $this->component))->render();
        }

        // A bypassed island is an island with no contents which is used when a component re-renders
        // and shouldn't touch the island. This is different from a skipped island which can have
        // a placeholder. We don't want the skipped island placeholder to be rendered with the
        // component on a subsequent render, hence why we need a bypass island...
        if ($this->render === 'bypass') {
            return (new BypassedIsland($this->key, $this->name))->render();
        }

        if ($this->render === 'skip') {
            return (new SkippedIsland($this->key, $this->name, $this->placeholder))->render();
        }

        if ($this->lazy) {
            return (new LazyIsland($this->key, $this->name, $this->placeholder))->render();
        }

        if ($this->defer) {
        return (new DeferredIsland($this->key, $this->name, $this->placeholder))->render();
        }

        return (new DefaultIsland($this->key, $this->view, $this->component))->render();
    }

    public function toJson($options = 0)
    {
        $render = $this->render;

        // This first render happens, but on the next render a bypassed island will be rendered in it's place...
        if ($render === 'once' || $render === 'skip') {
            $render = 'bypass';
        }

        return [
            'name' => $this->name,
            'key' => $this->key,
            'mode' => $this->mode,
            'render' => $render,
            'poll' => $this->poll,
        ];
    }

    public function __toString()
    {
        return $this->render();
    }

    public function toHtml()
    {
        return $this->render(force: true);
    }
}
