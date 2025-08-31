<?php

namespace Livewire\V4\Islands;

class LazyIsland
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
            . "<div x-intersect=\"\$wire.\$island('{$this->name}')\">{$placeholderContent}</div>"
            . "<!--[if ENDISLAND:{$this->key}]><![endif]-->";
    }
}
