<?php

namespace Livewire\Concerns;

use Illuminate\Support\Facades\Hash;

trait TracksDirtySyncedInputs
{
    protected $hashes = [];
    protected $exemptFromHashing = [];

    protected function removeFromDirtyInputsList($name)
    {
        $this->exemptFromHashing[] = $name;
    }

    public function hashCurrentObjectPropertiesForEasilyDetectingChangesLater()
    {
        $this->hashes = collect($this->getPublicPropertiesDefinedBySubClass())
            ->filter(function ($value, $prop) {
                // For now, I only care about strings & numbers. We can add more things to
                // dirty check later, but I want to keep things light and fast.
                return is_null($value)
                    || is_string($value)
                    || is_numeric($value);
            })
            ->mapWithKeys(function ($value, $prop) {
                // Using crc32 because it's fast, and this doesn't have to be secure.
                return [$prop => crc32($value)];
            })
            ->toArray();
    }

    public function rehashProperty($property)
    {
        $this->hashes[$property] = crc32($this->getPropertyValue($property));
    }

    public function dirtyInputs()
    {
        return collect($this->hashes)
            ->reject(function ($hash, $prop) {
                return in_array($prop, $this->exemptFromHashing);
            })
            ->filter(function ($hash, $prop) {
                return is_string($this->getPropertyValue($prop)) || is_numeric($this->getPropertyValue($prop)) || is_null($this->getPropertyValue($prop));
            })
            ->filter(function ($hash, $prop) {
                // Only return the hashes/props that have changed.
                return crc32($this->getPropertyValue($prop)) !== $hash;
            })
            ->keys()
            ->toArray();
    }
}
