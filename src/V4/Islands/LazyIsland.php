<?php

namespace Livewire\V4\Islands;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class LazyIsland implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
        public string $mode = 'replace',
    ) {}

    public function render()
    {
        return "<!--[if ISLAND:{$this->name}:lazy]><![endif]-->"
            . "<div x-intersect=\"\$wire.\$island('{$this->name}')\">Loading...</div>"
            . "<!--[if ENDISLAND:{$this->name}]><![endif]-->";
    }

    public function toJson($options = 0)
    {
        return [
            'name' => $this->name,
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