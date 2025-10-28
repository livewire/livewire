<?php

namespace Livewire\Features\SupportSlots;

use Illuminate\Contracts\Support\Htmlable;
use Livewire\Features\SupportSlots\Slot;
use Livewire\Component;

class SlotProxy implements Htmlable
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
}