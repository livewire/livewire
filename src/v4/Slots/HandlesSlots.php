<?php

namespace Livewire\V4\Slots;

use Illuminate\Support\Collection;

trait HandlesSlots
{
    protected Collection $slots;
    protected array $trackedSlots = [];

    public function withSlots(array $slots, $parent = null): self
    {
        $parentId = $parent && method_exists($parent, 'getId') ? $parent->getId() : null;

        $this->slots = collect($slots)->map(function ($content, $name) use ($parentId) {
            return new Slot($name, $content, $parentId);
        });

        return $this;
    }

    public function trackSlotForSubsequentRenders(string $name, string $content): void
    {
        $this->trackedSlots[$name] = $content;
    }

    public function getTrackedSlots(): array
    {
        return $this->trackedSlots;
    }

    public function hasSlots(): bool
    {
        return !empty($this->trackedSlots) || (isset($this->slots) && $this->slots->isNotEmpty());
    }

    public function getSlot(string $name = 'default'): ?Slot
    {
        return $this->slots?->get($name);
    }

    public function hasSlot(string $name = 'default'): bool
    {
        return $this->slots?->has($name) ?? false;
    }

    public function getSlots(): Collection
    {
        return $this->slots ?? collect();
    }

    public function initializeSlots(): void
    {
        if (!isset($this->slots)) {
            $this->slots = collect();
        }
    }

    public function getSlotObjectForView()
    {
        return new SlotProxy($this->slots ?? collect());
    }
}

class SlotProxy
{
    public function __construct(protected Collection $slots) {}

    public function __invoke($name = 'default')
    {
        return $this->get($name);
    }

    public function get($name = 'default')
    {
        return $this->slots->get($name) ?? new Slot($name, '');
    }

    public function has($name): bool
    {
        return $this->slots->has($name);
    }

    public function toHtml(): string
    {
        return $this->get('default')->toHtml();
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }
}