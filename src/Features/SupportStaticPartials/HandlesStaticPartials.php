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

    // A memo of the most recently rendered static so that we can strip it from the current for the hash/checksum...
    protected $lastStaticContent = null;

    // Begin capturing a new partial marked as `@static`...
    public function startStatic($key)
    {
        ob_start();
    }

    // End capturing a new partial marked as `@static`...
    public function endStatic()
    {
        $output = ob_get_clean();

        // Generate a hash (checksum) of the static contents. Disregarding the contents
        // of any slots or nested statics so that it stays deterministic...
        $hash = $this->generateHashOfStaticContents($output);

        // If the static has already been rendered in the browser, we'll "bypass it"...
        if ($this->shouldBypassStatic($hash)) {
            // It's important to add "bypassedStatics" in "endStatic" rather than
            // "startStatic" so that the order is "depth-first". This allows
            // JavaScript to recursively regex easily and reliably...
            $this->bypassedStatics[] = $hash;

            $tmp = "[static:$hash]";

            $pattern = '/\[dynamic\](.*?)\[enddynamic\]/s';

            $output = preg_replace_callback($pattern, function ($matches) use ($hash, &$tmp) {
                $slot = $matches[1];

                $slot = Utils::insertAttributesIntoHtmlRoot($slot, ['wire:dynamic' => $hash], strict: false);

                $tmp .= "[dynamic:$hash]{$slot}[enddynamic:$hash]";

                return $matches[0];
            }, $output);

            $tmp .= "[endstatic:$hash]";

            $output = $tmp;
        } else {
            $this->newStatics[] = $hash;

            $pattern = '/\[dynamic\](.*?)\[enddynamic\]/s';

            $output = preg_replace_callback($pattern, function ($matches) use ($hash) {
                $slot = $matches[1];

                return Utils::insertAttributesIntoHtmlRoot($slot, ['wire:dynamic' => $hash], strict: false);
            }, $output);

            // Add "wire:static" to the root of the static partial for browser identification...
            $output = Utils::insertAttributesIntoHtmlRoot($output, [
                'wire:static' => $hash,
            ]);
        }

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
        return '[dynamic]'.ob_get_clean().'[enddynamic]';
    }

    // Detect if the current static partial has been rendered and cached in the browser...
    public function shouldBypassStatic($hash)
    {
        return in_array($hash, $this->previousStatics);
    }

    // Generate a checksum/hash of the contents of the static for comparison between requests.
    public function generateHashOfStaticContents($output)
    {
        // Remove any nested statics...
        if ($this->lastStaticContent) {
            $output = (string) str($output)->replaceFirst($this->lastStaticContent, '');
        }

        $pattern = '/\[dynamic\].*?\[enddynamic\]/s';

        // Use preg_replace to remove all matched patterns from the string
        $output = preg_replace($pattern, '', $output);

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
