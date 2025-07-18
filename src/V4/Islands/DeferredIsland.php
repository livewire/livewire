<?php

namespace Livewire\V4\Islands;

class DeferredIsland
{
    public function __construct(
        public string $key,
        public string $name,
    ) {}

    public function render()
    {
        return "<!--[if ISLAND:{$this->key}]><![endif]-->"
            . "<div wire:init=\"\$island('{$this->name}')\">Loading...</div>"
            . "<!--[if ENDISLAND:{$this->key}]><![endif]-->";
    }
}