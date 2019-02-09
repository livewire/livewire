<?php

namespace Livewire\Concerns;

use Illuminate\Support\Facades\Hash;

trait TracksDirtySyncedInputs
{
    protected $hashes = [];
    protected $exemptFromHashDiffing = [
        'hashes', 'exemptFromHashDiffing',
    ];

    // Not in love with these method names.
    public function resetObjectPropertyHashes()
    {
        $this->hashes = [];
        // Sorry for this duplication, but it's clearer at the moment.
        $this->exemptFromHashDiffing = [
            'hashes', 'exemptFromHashDiffing',
        ];
    }

    protected function removeFromDirtyPropertiesList($name)
    {
        $this->exemptFromHashDiffing[] = $name;
    }

    public function hashCurrentObjectPropertiesForEasilyDetectingChangesLater()
    {
        $this->hashes = collect($this->getObjectProperties())
            ->filter(function ($prop) {
                return ! in_array($prop, $this->exemptFromHashDiffing);
            })
            ->filter(function ($prop) {
                // For now, I only care about strings & numbers. We can add more things to
                // dirty check later, but want to keep things light and fast.
                return is_null($propValue = $this->{$prop})
                    || is_string($propValue)
                    || is_numeric($propValue);
            })
            ->mapWithKeys(function ($prop) {
                // Using crc32 because it's fast, and this doesn't have to be secure.
                return [$prop => crc32($this->{$prop})];
            })
            ->toArray();
    }

    public function dirtyInputs()
    {
        return collect($this->hashes)
            ->filter(function ($hash, $prop) {
                // Only return the hashes/props that have changed.
                return crc32($this->{$prop}) !== $hash;
            })
            ->keys()
            ->toArray();
    }
}
