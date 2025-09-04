<?php

namespace Livewire\V4\Islands;

class DeferredIsland
{
    public function __construct(
        public string $key,
        public string $name,
        public ?string $placeholder = null,
    ) {}

    public function render()
    {
        $placeholderContent = '';

        if (view()->exists($this->placeholder)) {
            $placeholderContent = view($this->placeholder)->render();
        }

        return "<!--[if ISLAND:{$this->key}:placeholder]><![endif]-->"
            . "<div wire:init=\"\$island('{$this->name}')\">{$placeholderContent}</div>"
            . "<!--[if ENDISLAND:{$this->key}]><![endif]-->";
    }
}
