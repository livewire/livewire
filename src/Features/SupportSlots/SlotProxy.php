<?php

namespace Livewire\Features\SupportSlots;

use ArrayAccess;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Features\SupportSlots\Slot;
use Livewire\Component;

class SlotProxy implements Htmlable, ArrayAccess
{
    public function __construct(
        protected Component $component,
        protected array $slots,
    ) {}

    public function __invoke($name = 'default')
    {
        return $this->get($name);
    }

    public function find($name)
    {
        foreach ($this->slots as $slot) {
            if ($slot->getName() === $name) {
                return $slot;
            }
        }

        return null;
    }

    public function get($name = 'default')
    {
        return $this->find($name) ?? new Slot($name, '', $this->component->getId());
    }

    public function has($name): bool
    {
        return $this->find($name) !== null;
    }

    public function toHtml(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->get('default')->toHtml();
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        //
    }

    public function offsetUnset($offset): void
    {
        //
    }
}