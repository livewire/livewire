<?php

namespace Livewire\V4\Partials;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class LazyPartial implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
    ) {}

    public function render()
    {
        return "<!--[if PARTIAL:{$this->name}:lazy]><![endif]-->"
            . "<div x-intersect=\"\$wire.\$partial('{$this->name}')\">Loading...</div>"
            . "<!--[if ENDPARTIAL:{$this->name}]><![endif]-->";
    }

    public function toJson($options = 0)
    {
        return [
            'name' => $this->name,
            'mode' => 'lazy',
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
