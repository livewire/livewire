<?php

namespace Livewire\V4\Refs;

trait HandlesRefs
{
    protected ?string $ref = null;

    public function withRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function hasRef(): bool
    {
        return $this->ref !== null;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }
}