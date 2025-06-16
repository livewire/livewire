<?php

namespace Livewire\V4\Islands;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class DeferredIsland implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
    ) {}

    public function __toString()
    {
        return "<!--[if ISLAND:{$this->name}:defer]><![endif]-->"
            . "<div wire:init=\"\$island('{$this->name}')\">Loading...</div>"
            . "<!--[if ENDISLAND:{$this->name}]><![endif]-->";
    }

    public function toHtml()
    {
        return $this->__toString();
    }

    public function toJson($options = 0)
    {
        return json_encode([
            'name' => $this->name,
            'mode' => 'defer',
        ], $options);
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'mode' => 'defer',
        ];
    }

    public function append()
    {
        return $this;
    }

    public function prepend()
    {
        return $this;
    }
}
