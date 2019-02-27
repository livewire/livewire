<?php

namespace Livewire\Concerns;

use Closure;
use Illuminate\Queue\SerializableClosure;

trait CanBeSerialized
{
    public function __sleep()
    {
        // Prepare all callbacks for serialization.
        // PHP cannot serialize closures on its own.
        foreach ($props = $this->getPublicPropertiesDefinedBySubClass() as $prop => $value) {
            if ($value instanceof Closure) {
                $this->{$prop} = new SerializableClosure($value);
            }
        }

        // The _sleep method expects you to return all the class properties you
        // want to be serialized. Kinda weird, but whatever.
        return collect((new \ReflectionClass($this))->getProperties())
            ->map->getName()->toArray();
    }

    public function __wakeup()
    {
        //
    }
}
