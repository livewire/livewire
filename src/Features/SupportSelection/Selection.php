<?php

namespace Livewire\Features\SupportSelection;

use Illuminate\Contracts\Support\Arrayable;

class Selection implements Arrayable, \Countable, \IteratorAggregate, \JsonSerializable
{
    // A selection is dual-mode. In "include" mode $keys IS the selection.
    // In "except" mode the selection is every result EXCEPT $keys — the
    // shape "select all" needs on paginated sets, where enumerating every
    // key is impossible...
    public function __construct(
        protected array $keys = [],
        protected string $mode = 'include',
    ) {}

    public function all(): array
    {
        $this->ensureIncludeMode(__FUNCTION__);

        return $this->keys;
    }

    public function except(): array
    {
        return $this->isAll() ? $this->keys : [];
    }

    public function isAll(): bool
    {
        return $this->mode === 'except';
    }

    public function isAllSelected(): bool
    {
        return $this->isAll() && count($this->keys) === 0;
    }

    public function any(): bool
    {
        return $this->isAll() || count($this->keys) > 0;
    }

    public function isEmpty(): bool
    {
        return ! $this->any();
    }

    public function count(): int
    {
        $this->ensureIncludeMode(__FUNCTION__);

        return count($this->keys);
    }

    public function contains($key): bool
    {
        // Loose comparison — checkbox values arrive as strings while
        // server-side keys are often integers...
        $has = in_array($key, $this->keys);

        return $this->isAll() ? ! $has : $has;
    }

    public function select($key): static
    {
        $this->isAll() ? $this->removeKey($key) : $this->addKey($key);

        return $this;
    }

    public function deselect($key): static
    {
        $this->isAll() ? $this->addKey($key) : $this->removeKey($key);

        return $this;
    }

    public function toggle($key): static
    {
        $this->contains($key) ? $this->deselect($key) : $this->select($key);

        return $this;
    }

    public function selectAll(): static
    {
        $this->keys = [];
        $this->mode = 'except';

        return $this;
    }

    public function clear(): static
    {
        $this->keys = [];
        $this->mode = 'include';

        return $this;
    }

    public function toArray(): array
    {
        $this->ensureIncludeMode(__FUNCTION__);

        return $this->keys;
    }

    public function getIterator(): \ArrayIterator
    {
        $this->ensureIncludeMode(__FUNCTION__);

        return new \ArrayIterator($this->keys);
    }

    public function jsonSerialize(): array
    {
        $this->ensureIncludeMode(__FUNCTION__);

        return $this->keys;
    }

    protected function addKey($key): void
    {
        if (! in_array($key, $this->keys)) $this->keys[] = $key;
    }

    protected function removeKey($key): void
    {
        $this->keys = array_values(array_filter($this->keys, fn ($i) => $i != $key));
    }

    // In except mode there is no key list to hand out — enumerating the
    // selection requires the full result set. Failing loudly beats a
    // whereIn() that silently targets the WRONG rows (the exceptions)...
    protected function ensureIncludeMode(string $method): void
    {
        if ($this->isAll()) {
            throw new \RuntimeException(
                'Livewire: ['.$method.'] is not available while a selection is in select-all mode — '.
                'the selected keys cannot be enumerated. Check isAll() and scope your query with except() instead.'
            );
        }
    }
}
