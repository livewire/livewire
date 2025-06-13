<?php

namespace Livewire\V4\Slots;

use Illuminate\Contracts\Support\Htmlable;

class SlotProxy implements Htmlable
{
    public function __construct(protected array $slots) {}

    public function __invoke($name = 'default')
    {
        return $this->get($name);
    }

    public function get($name = 'default')
    {
        return $this->slots[$name] ?? new Slot($name, '');
    }

    public function has($name): bool
    {
        return isset($this->slots[$name]);
    }

    public function toHtml(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->get('default')->toHtml();
    }
}