<?php

namespace Livewire;

use Stringable;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\Contracts\Support\Htmlable;

class WireDirective implements Htmlable, Stringable
{
    public function __construct(
        public $name,
        public $directive,
        public $value,
    ) {}

    public function name()
    {
        return $this->name;
    }

    public function directive()
    {
        return $this->directive;
    }

    public function value()
    {
        return $this->value;
    }

    public function modifiers()
    {
        return str($this->directive)
            ->replace("wire:{$this->name}", '')
            ->explode('.')
            ->filter()->values();
    }

    public function hasModifier($modifier)
    {
        return $this->modifiers()->contains($modifier);
    }

    public function toHtml()
    {
        return (new ComponentAttributeBag([$this->directive => $this->value]))->toHtml();
    }

    public function toString()
    {
        return (string) $this;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
