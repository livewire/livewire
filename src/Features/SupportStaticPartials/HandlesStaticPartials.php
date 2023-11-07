<?php

namespace Livewire\Features\SupportStaticPartials;

use Livewire\Drawer\Utils;

trait HandlesStaticPartials
{
    // Previously cached statics in the browser that we can bypass this time...
    protected $previousStatics = [];

    // New static hashes to send to the browser for caching...
    protected $newStatics = [];

    // A list of bypassed statics this request to be replaced by the cache in the browser before morphing...
    protected $bypassedStatics = [];

    // A running list of nested static keys so that we can track where we are in the hierarchy as we go...
    protected $staticStack = [];

    // A memo of the most recently rendered static so that we can strip it from the current for the hash/checksum...
    protected $lastStaticContent = null;

    // A store of all dynamic slots (marked with @dynamic) for injection and hash generation...
    protected $dynamicsByCurrentStackIdx = [];

    // Begin capturing a new partial marked as `@static`...
    public function startStatic($key)
    {
        $this->staticStack[] = $key;

        ob_start();
    }

    // End capturing a new partial marked as `@static`...
    public function endStatic()
    {
        $currentStackIdx = array_key_last($this->staticStack);
        $key = array_pop($this->staticStack);

        $output = ob_get_clean();

        // Generate a hash (checksum) of the static contents. Disregarding the contents
        // of any slots or nested statics so that it stays deterministic...
        $hash = $this->generateHashOfStaticContents($output, $currentStackIdx);

        // If the static has already been rendered in the browser, we'll "bypass it"...
        if ($this->shouldBypassStatic($hash)) {
            // It's important to add "bypassedStatics" in "endStatic" rather than
            // "startStatic" so that the order is "depth-first". This allows
            // JavaScript to recursively regex easily and reliably...
            $this->bypassedStatics[] = $hash;

            $tmp = "[static:$hash]";

            // Insert any dynamic portions as a "slot" inside the static placeholder...
            foreach ($this->dynamicsByCurrentStackIdx[$currentStackIdx] ?? [] as $slot) {
                // Add "wire:dynamic" back to the dynamic slot so it doesn't get removed in the live DOM...
                $slot = Utils::insertAttributesIntoHtmlRoot($slot, ['wire:dynamic' => $hash], strict: false);

                $tmp .= "[dynamic:$hash]{$slot}[enddynamic:$hash]";
            }

            $tmp .= "[endstatic:$hash]";

            $output = $tmp;
        } else {
            $this->newStatics[] = $hash;

            // Add "wire:dynamic" to the roots of all the dynamic slots...
            foreach ($this->dynamicsByCurrentStackIdx[$currentStackIdx] ?? [] as $slot) {
                $output = (string) str($output)->replaceFirst($slot,
                    Utils::insertAttributesIntoHtmlRoot($slot, ['wire:dynamic' => $hash], strict: false)
                );
            }

            // Add "wire:static" to the root of the static partial for browser identification...
            $output = Utils::insertAttributesIntoHtmlRoot($output, [
                'wire:static' => $hash,
            ]);
        }

        // Clear the stored dynamics for memory safety...
        unset($this->dynamicsByCurrentStackIdx[$currentStackIdx]);

        // Store this static's content to be removed from a parent's content when generating a checksum/hash...
        $this->lastStaticContent = $output;

        return $output;
    }

    // Begin capturing a new partial marked as `@dynamic`...
    public function startDynamic()
    {
        ob_start();
    }

    // End capturing a new partial marked as `@dynamic`...
    public function endDynamic()
    {
        $currentStackIdx = array_key_last($this->staticStack);

        if (! isset($this->dynamicsByCurrentStackIdx[$currentStackIdx])) {
            $this->dynamicsByCurrentStackIdx[$currentStackIdx] = [];
        }

        $output = ob_get_clean();

        // Store the contents of this dynamic slot for later use by `@endstatic`
        $this->dynamicsByCurrentStackIdx[$currentStackIdx][] = $output;

        return $output;
    }

    // Detect if the current static partial has been rendered and cached in the browser...
    public function shouldBypassStatic($hash)
    {
        return in_array($hash, $this->previousStatics);
    }

    // Generate a checksum/hash of the contents of the static for comparison between requests.
    public function generateHashOfStaticContents($output, $currentStackIdx)
    {
        // Remove any nested statics...
        if ($this->lastStaticContent) {
            $output = (string) str($output)->replaceFirst($this->lastStaticContent, '');
        }

        // Remove any nested slots because their contents are allowed to change...
        $slots = $this->dynamicsByCurrentStackIdx[$currentStackIdx] ?? [];

        foreach ($slots as $slot) {
            $output = (string) str($output)->replaceFirst($slot, '');
        }

        // Use crc32 becuase it's fast...
        return crc32($output);
    }

    public function setPreviousStatics($statics)
    {
        $this->previousStatics = $statics;
    }

    public function getAllStatics()
    {
        return array_merge($this->previousStatics, $this->newStatics);
    }

    public function getNewStatics()
    {
        return $this->newStatics;
    }

    public function getBypassedStatics()
    {
        return $this->bypassedStatics;
    }
}
