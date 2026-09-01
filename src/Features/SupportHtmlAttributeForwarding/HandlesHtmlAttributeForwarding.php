<?php

namespace Livewire\Features\SupportHtmlAttributeForwarding;

use Illuminate\Contracts\Support\Htmlable;

trait HandlesHtmlAttributeForwarding
{
    protected array $htmlAttributes = [];

    public function withHtmlAttributes(array $attributes): self
    {
        $this->htmlAttributes = $attributes;

        return $this;
    }

    public function getHtmlAttributes(): array
    {
        return array_filter(
            $this->htmlAttributes,
            fn ($value) => ! is_array($value) && (! is_object($value) || $value instanceof Htmlable)
        );
    }
}