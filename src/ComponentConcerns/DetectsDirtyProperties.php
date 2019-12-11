<?php

namespace Livewire\ComponentConcerns;

use Illuminate\Support\Arr;

trait DetectsDirtyProperties
{
    protected $propertyHashes = [];

    public function hashPropertiesForDirtyDetection()
    {
        foreach ($this->getPublicPropertiesDefinedBySubClass() as $property => $value) {
            if (is_array($value)) {
                foreach (Arr::dot($value, $property.'.') as $key => $value) {
                    $this->propertyHashes[$key] = $this->hashProperty($key);
                }
            } else {
                $this->propertyHashes[$property] = $this->hashProperty($property);
            }
        }
    }

    public function rehashProperty($name)
    {
        $this->propertyHashes[$name] = $this->hashProperty($name);
    }

    public function hashProperty($name)
    {
        $value = $this->getPropertyValue($name);

        if (! is_null($value) && ! is_string($value) && ! is_numeric($value)) {
            $value = method_exists($value, '__toString')
                ? (string) $value
                : json_encode($value);
        }

        // Using crc32 because it's fast, and this doesn't have to be secure.
        return crc32($value);
    }

    public function getDirtyProperties()
    {
        return collect($this->propertyHashes)
            ->filter(function ($hash, $prop) {
                // Only return the propertyHashes/props that have changed.
                return $this->hashProperty($prop) !== $hash;
            })
            ->keys()
            ->toArray();
    }
}
