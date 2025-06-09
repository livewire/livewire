<?php

namespace Livewire\Features\SupportPartials;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Htmlable;

class Partial implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
        public string $view,
        public array $data = [],
        public string $mode = 'replace',
    ) {}

    public function render()
    {
        $output = view($this->view, $this->data)->render();

        return "<!--[if PARTIAL:{$this->name}]><![endif]-->"
            . $output
            . "<!--[if ENDPARTIAL:{$this->name}]><![endif]-->";
    }

    public function prepend()
    {
        $this->mode = 'prepend';

        return $this;
    }

    public function append()
    {
        $this->mode = 'append';

        return $this;
    }

    public function toJson($options = 0)
    {
        return [
            'name' => $this->name,
            'mode' => $this->mode,
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
