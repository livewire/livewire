<?php

namespace Livewire\V4\Islands;

class LazyIsland
{
    public function __construct(
        public string $key,
        public string $name,
    ) {}

    public function render()
    {
        return "<!--[if ISLAND:{$this->key}]><![endif]-->"
            . "<div x-intersect=\"\$wire.\$island('{$this->name}')\">Loading...</div>"
            . "<!--[if ENDISLAND:{$this->key}]><![endif]-->";
    }
}