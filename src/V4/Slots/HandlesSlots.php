<?php

namespace Livewire\V4\Slots;

trait HandlesSlots
{
    protected array $slots = [];

    protected array $slotsForSkippedChildRenders = [];

    public function getSlotsForSkippedChildRenders()
    {
        return $this->slotsForSkippedChildRenders;
    }

    public function withSlots(array $slots, $parent = null): self
    {
        $parentId = $parent && method_exists($parent, 'getId') ? $parent->getId() : null;

        foreach ($slots as $name => $content) {
            $this->slots[$name] = new Slot($name, $content, $parentId);
        }

        return $this;
    }

    public function withPlaceholderSlots(array $slots): self
    {
        foreach ($slots as $name => $slot) {
            $this->slots[$name] = new PlaceholderSlot($name, $slot['parentId']);
        }

        return $this;
    }

    public function withChildSlots(array $slots, $childId)
    {
        $this->slotsForSkippedChildRenders[$childId] = [];

        foreach ($slots as $name => $content) {
            $this->slotsForSkippedChildRenders[$childId][$name] = (new Slot($name, $content, $this->getId()))->toHtml();
        }
    }

    public function getSlots()
    {
        return $this->slots;
    }
}