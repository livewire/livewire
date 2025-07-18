<?php

namespace Livewire\V4\Islands;

class SkippedIsland
{
    public function __construct(
        public string $key,
    ) {}

    public function render()
    {
        return "<!--[if ISLAND:{$this->key}]><![endif]-->"
            . "<!--[if ENDISLAND:{$this->key}]><![endif]-->";
    }
}