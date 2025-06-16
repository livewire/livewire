<?php

namespace Livewire\V4\Islands;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Jsonable;

class Island implements \Stringable, Htmlable, Jsonable
{
    public function __construct(
        public string $name,
        public string $view,
        public array $data,
        public $component,
        public ?string $mode = null,
    ) {}

    public function toHtml()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        $content = (string) view($this->view, $this->data)->render();

        return "<!--[if ISLAND:{$this->name}:{$this->mode}]><![endif]-->"
            . $content
            . "<!--[if ENDISLAND:{$this->name}]><![endif]-->";
    }

    public function toJson($options = 0)
    {
        return json_encode([
            'name' => $this->name,
            'view' => $this->view,
            'data' => $this->data,
            'mode' => $this->mode,
        ], $options);
    }

    public function append()
    {
        $this->mode = 'append';

        return $this;
    }

    public function prepend()
    {
        $this->mode = 'prepend';

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'view' => $this->view,
            'data' => $this->data,
            'mode' => $this->mode,
        ];
    }
}
