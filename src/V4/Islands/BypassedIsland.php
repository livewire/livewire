<?php

namespace Livewire\V4\Islands;

class BypassedIsland
{
    public function __construct(
        public string $key,
        public string $name,
    ) {}

    public function render()
    {
        return "<!--[if ISLAND:{$this->key}]><![endif]-->"
            . "<!--[if ENDISLAND:{$this->key}]><![endif]-->";
    }
}
