<?php

namespace Livewire\V4\Partials;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class DeferredPartial implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
    ) {}

    public function render()
    {
        return "<!--[if PARTIAL:{$this->name}:defer]><![endif]-->"
            . "<div wire:init=\"\$partial('{$this->name}')\">Loading...</div>"
            . "<!--[if ENDPARTIAL:{$this->name}]><![endif]-->";
    }

    public function toJson($options = 0)
    {
        return [
            'name' => $this->name,
            'mode' => 'defer',
            'content' => $this->render(),
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
