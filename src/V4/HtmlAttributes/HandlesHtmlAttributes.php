<?php

namespace Livewire\V4\HtmlAttributes;

trait HandlesHtmlAttributes
{
    protected array $htmlAttributes = [];

    public function withHtmlAttributes(array $attributes): self
    {
        $this->htmlAttributes = $attributes;

        return $this;
    }

    public function getHtmlAttributes(): array
    {
        return $this->htmlAttributes;
    }
}