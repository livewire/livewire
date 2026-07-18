<?php

namespace Livewire\Features\SupportSelection;

use Illuminate\Contracts\Support\Arrayable;

class Selection implements Arrayable, \Countable, \IteratorAggregate, \JsonSerializable
{
    // @todo: Dual-mode selection ("all results except [...]" for select-all
    // across paginated sets) will add a mode/except pair alongside $keys...
    public function __construct(
        protected array $keys = [],
    ) {}

    public function all(): array
    {
        return $this->keys;
    }

    public function any(): bool
    {
        return count($this->keys) > 0;
    }

    public function isEmpty(): bool
    {
        return ! $this->any();
    }

    public function count(): int
    {
        return count($this->keys);
    }

    public function contains($key): bool
    {
        // Loose comparison — checkbox values arrive as strings while
        // server-side keys are often integers...
        return in_array($key, $this->keys);
    }

    public function select($key): static
    {
        if (! $this->contains($key)) $this->keys[] = $key;

        return $this;
    }

    public function deselect($key): static
    {
        $this->keys = array_values(array_filter($this->keys, fn ($i) => $i != $key));

        return $this;
    }

    public function clear(): static
    {
        $this->keys = [];

        return $this;
    }

    public function toArray(): array
    {
        return $this->keys;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->keys);
    }

    public function jsonSerialize(): array
    {
        return $this->keys;
    }
}
