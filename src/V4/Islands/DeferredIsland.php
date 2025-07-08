<?php

namespace Livewire\V4\Islands;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class DeferredIsland implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
        public string $key,
        public string $mode = 'replace',
    ) {}

    public function render()
    {
        return "<!--[if ISLAND:{$this->name}:{$this->key}:defer]><![endif]-->"
            . "<div wire:init=\"\$island('{$this->name}')\">Loading...</div>"
            . "<!--[if ENDISLAND:{$this->name}:{$this->key}]><![endif]-->";
    }

    public function toJson($options = 0)
    {
        return [
            'name' => $this->name,
            'key' => $this->key,
            'mode' => $this->mode,
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