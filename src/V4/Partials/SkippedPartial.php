<?php

namespace Livewire\V4\Partials;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class SkippedPartial implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
    ) {}

    public function render()
    {
        return "<!--[if PARTIAL:{$this->name}:skip]><![endif]-->"
            . "<!--[if ENDPARTIAL:{$this->name}]><![endif]-->";
    }

    public function toJson($options = 0)
    {
        return [
            'name' => $this->name,
            'mode' => 'skip',
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
