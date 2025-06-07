<?php

namespace Livewire\V4\Slots;

use Illuminate\Contracts\Support\Htmlable;

class PlaceholderSlot implements Htmlable
{
    public function __construct(
        public string $name,
        public ?string $parentComponentId = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getParentId(): ?string
    {
        return $this->parentComponentId;
    }

    public function toHtml(): string
    {
        return $this->wrapInCommentMarkers('');
    }

    protected function wrapInCommentMarkers(): string
    {
        if ($this->parentComponentId) {
            return "<!--[if SLOT:{$this->name}:{$this->parentComponentId}]><![endif]-->"
                . ''
                . "<!--[if ENDSLOT:{$this->name}:{$this->parentComponentId}]><![endif]-->";
        }

        return "<!--[if SLOT:{$this->name}]><![endif]-->"
            . ''
            . "<!--[if ENDSLOT:{$this->name}]><![endif]-->";
    }
}
