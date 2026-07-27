<?php

namespace Livewire\Features\SupportSelection;

use Illuminate\Contracts\Support\Arrayable;

class Selection implements Arrayable, \Countable, \IteratorAggregate, \JsonSerializable
{
    // A selection is dual-mode. In "include" mode $keys IS the selection.
    // In "except" mode the selection is every result EXCEPT $keys — the
    // shape "select all" needs on paginated sets, where enumerating every
    // key is impossible...
    protected ?int $total = null;

    public function __construct(
        protected array $keys = [],
        protected string $mode = 'include',
    ) {}

    // Totals are deliberately opt-in: a selection works fine never knowing
    // one ("All selected (3 excluded)" needs no total). Feed one when you
    // want a computed all-mode count() — accepts a paginator or an int...
    public function setTotal($total): static
    {
        $this->total = $total instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
            ? $total->total()
            : (int) $total;

        return $this;
    }

    public function total(): ?int
    {
        return $this->total;
    }

    public function keys(): array
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

    public function count(?int $total = null): int
    {
        if (! $this->isAll()) return count($this->keys);

        $total ??= $this->total;

        // Without a total, a select-all count is unknowable — blow up
        // rather than report the exception count as the selection...
        if ($total === null) {
            throw new \RuntimeException(
                'Livewire: [count] is unknowable while a selection is in select-all mode without a total. '.
                'Feed one with setTotal($paginator) or pass it directly: count($total).'
            );
        }

        return max(0, $total - count($this->keys));
    }

    public function contains($key): bool
    {
        // Loose comparison — checkbox values arrive as strings while
        // server-side keys are often integers...
        $has = in_array($key, $this->keys);

        return $this->isAll() ? ! $has : $has;
    }

    // Collections answer membership checks as both has() and contains() —
    // same here, so whichever word users guess works...
    public function has($key): bool
    {
        return $this->contains($key);
    }

    // Each mutator accepts a single key or an array of keys — selecting
    // straight from data is one call: select($rows->pluck('id')->all())...
    public function select($key): static
    {
        foreach (is_array($key) ? $key : [$key] as $single) {
            $this->isAll() ? $this->removeKey($single) : $this->addKey($single);
        }

        return $this;
    }

    public function deselect($key): static
    {
        foreach (is_array($key) ? $key : [$key] as $single) {
            $this->isAll() ? $this->addKey($single) : $this->removeKey($single);
        }

        return $this;
    }

    public function toggle($key): static
    {
        foreach (is_array($key) ? $key : [$key] as $single) {
            $this->contains($single) ? $this->deselect($single) : $this->select($single);
        }

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

    // Form objects call this operation reset() — same word here so either
    // primitive answers to the vocabulary users already know...
    public function reset(): static
    {
        return $this->clear();
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
