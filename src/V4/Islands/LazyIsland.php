<?php

namespace Livewire\V4\Islands;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class LazyIsland implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
        public string $key,
        public string $mode = 'replace',
        public ?string $placeholder = null,
    ) {}

    public function render()
    {
        $placeholderContent = 'Loading...';

        if (isset($this->placeholder)) {
            $placeholderContent = view($this->placeholder)->render();
        }

        return "<!--[if ISLAND:{$this->name}:{$this->key}:lazy]><![endif]-->"
            . "<div x-intersect=\"\$wire.\$island('{$this->name}')\">{$placeholderContent}</div>"
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