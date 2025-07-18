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
    ) {}

    public function render($force = false)
    {
        if ($force) {
            return (new DefaultIsland($this->key, $this->view, $this->component))->render();
        }

        if ($this->render === 'skip') {
            return (new SkippedIsland($this->key))->render();
        }
        
        if ($this->lazy) {
            return (new LazyIsland($this->key, $this->name))->render();
        }

        if ($this->defer) {
            return (new DeferredIsland($this->key, $this->name))->render();
        }

        return (new DefaultIsland($this->key, $this->view, $this->component))->render();
    }

    public function toJson($options = 0)
    {
        $render = $this->render;

        // This first render happens, but on the next render it will be skipped...
        if ($render === 'once') {
            $render = 'skip';
        }

        return [
            'name' => $this->name,
            'key' => $this->key,
            'mode' => $this->mode,
            'render' => $render,
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