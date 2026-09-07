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
        // Structured values like arrays and objects can't be rendered as HTML
        // attribute values, so only scalars and Htmlable objects are forwarded...
        return array_filter(
            $this->htmlAttributes,
            fn ($value) => is_scalar($value) || is_null($value) || $value instanceof Htmlable
        );
    }
}
