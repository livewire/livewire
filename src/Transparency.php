<?php

namespace Livewire;

use Traversable;

trait Transparency
{
    public $target;

    public function __toString()
    {
        return (string) $this->target;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->target[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->target[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->target[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->target[$offset]);
    }

    public function getIterator(): Traversable
    {
        return (function () {
            foreach ($this->target as $key => $value) {
                yield $key => $value;
            }
        })();
    }
}
