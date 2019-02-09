<?php

namespace Livewire\Concerns;

use Closure;
use Illuminate\Queue\SerializableClosure;

trait CanBeSerialized
{
    public function getObjectProperties()
    {
        return collect((new \ReflectionClass($this))->getProperties())
            ->map(function ($prop) {
                return $prop->getName();
            })->toArray();
    }

    public function __wakeup()
    {
        $this->hashCurrentObjectPropertiesForEasilyDetectingChangesLater();
    }

    public function __sleep()
    {
        $this->resetObjectPropertyHashes();

        // Prepare all callbacks for serialization.
        // PHP cannot serialize closures on its own.
        foreach ($props = $this->getObjectProperties() as $prop) {
            if ($this->{$prop} instanceof Closure) {
                $this->{$prop} = new SerializableClosure($this->{$prop});
            }
        }

        return $props;
    }
}
