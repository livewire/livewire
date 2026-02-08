<?php

namespace Livewire\Features\SupportSlots;

trait HandlesSlots
{
    /**
     * Parent concerns...
     */
    protected array $slotsForSkippedChildRenders = [];

    public function getSlotsForSkippedChildRenders()
    {
        return $this->slotsForSkippedChildRenders;
    }

    public function withChildSlots(array $slots, $childId)
    {
        foreach ($slots as $name => $content) {
            $this->slotsForSkippedChildRenders[] = (new Slot($name, $content, $childId, $this->getId()))->toHtml();
        }
    }

    /**
     * Child concerns...
     */
    protected array $slots = [];

    public function getSlots()
    {
        return $this->slots;
    }

    public function withSlots(array $slots, $parent = null): self
    {
        $parentId = $parent && method_exists($parent, 'getId') ? $parent->getId() : null;

        foreach ($slots as $name => $content) {
            $this->slots[] = new Slot($name, $content, $this->getId(), $parentId);
        }

        return $this;
    }

    public function withPlaceholderSlots(array $slots): self
    {
        foreach ($slots as $slot) {
            $this->slots[] = new PlaceholderSlot(
                $slot['name'],
                $slot['componentId'],
                $slot['parentId'],
            );
        }

        return $this;
    }


}