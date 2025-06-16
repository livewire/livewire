<?php

namespace Livewire\V4\Islands;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class LazyIsland implements \Stringable, Htmlable, Jsonable
{
    public function __construct(public string $name) {}

    public function __toString()
    {
        return "<!--[if ISLAND:{$this->name}:lazy]><![endif]-->"
            . "<div x-intersect=\"\$wire.\$island('{$this->name}')\">Loading...</div>"
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
            'mode' => 'lazy',
        ], $options);
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'mode' => 'lazy',
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
