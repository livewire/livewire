<?php

namespace Livewire\V4\Slots;

use Illuminate\Contracts\Support\Htmlable;
use Stringable;

class Slot implements Htmlable, Stringable
{
    public function __construct(
        public string $name,
        public string $content,
        public ?string $parentComponentId = null,
        public array $attributes = []
    ) {}

    public function toHtml(): string
    {
        return $this->content;
    }

    public function toString(): string
    {
        return $this->toHtml();
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    public function isEmpty(): bool
    {
        return empty(trim(strip_tags($this->content)));
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function hasActualContent(): bool
    {
        // Remove HTML comments and whitespace, then check if there's content
        $cleaned = preg_replace('/<!--.*?-->/s', '', $this->content);
        return !empty(trim(strip_tags($cleaned)));
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }
}
