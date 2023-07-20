<?php

namespace Livewire;

use Traversable;

trait Transparency
{
    public $target;

    function __toString()
    {
        return (string) $this->target;
    }

    function offsetExists(mixed $offset): bool
    {
        return isset($this->target[$offset]);
    }

    function offsetGet(mixed $offset): mixed
    {
        return $this->target[$offset];
    }

    function offsetSet(mixed $offset, mixed $value): void
    {
        $this->target[$offset] = $value;
    }

    function offsetUnset(mixed $offset): void
    {
        unset($this->target[$offset]);
    }

    function getIterator(): Traversable
    {
        return (function () {
            foreach ($this->target as $key => $value) {
                yield $key => $value;
            }
        })();
    }
}
