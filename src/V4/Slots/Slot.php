<?php

namespace Livewire\V4\Slots;

use Illuminate\Contracts\Support\Htmlable;

class Slot implements Htmlable
{
    public function __construct(
        public string $name,
        public string $content,
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
        return $this->wrapInCommentMarkers($this->content);
    }

    protected function wrapInCommentMarkers(string $content): string
    {
        if ($this->parentComponentId) {
            return "<!--[if SLOT:{$this->name}:{$this->parentComponentId}]><![endif]-->"
                . $content
                . "<!--[if ENDSLOT:{$this->name}:{$this->parentComponentId}]><![endif]-->";
        }

        return "<!--[if SLOT:{$this->name}]><![endif]-->"
            . $content
            . "<!--[if ENDSLOT:{$this->name}]><![endif]-->";
    }
}
