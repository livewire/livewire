<?php

namespace Livewire\Features\SupportStaticPartials;

use Livewire\Drawer\Utils;

trait HandlesStaticPartials
{
    protected $previousStatics = [];
    protected $newStatics = [];
    protected $staticStack = [];
    protected $renderedStatics = [];

    protected $lastStaticContent = null;

    public function setPreviousStatics($statics)
    {
        $this->previousStatics = $statics;
    }

    public function startStatic($key)
    {
        $this->staticStack[] = $key;

        ob_start();
    }

    public function endStatic()
    {
        $currentStackIdx = array_key_last($this->staticStack);
        $key = array_pop($this->staticStack);

        $output = ob_get_clean();

        $hash = $this->generateHashOfStatic($output, $currentStackIdx);

        if ($this->shouldBypassStatic($hash)) {
            // It's important to add "renderedStatics" in "endStatic" rather than
            // "startStatic" so that the order is "depth-first". This allows
            // JavaScript to recursively regex easily and reliably...
            $this->renderedStatics[] = $hash;

            $tmp = "[STATICSTART:$hash]";

            foreach ($this->dynamicsByCurrentStackIdx[$currentStackIdx] ?? [] as $slot) {
                $slot = Utils::insertAttributesIntoHtmlRoot($slot, ['wire:dynamic' => $hash], strict: false);
                $tmp .= "[DYNAMICSTART:$hash]{$slot}[DYNAMICEND:$hash]";
            }

            unset($this->dynamicsByCurrentStackIdx[$currentStackIdx]);

            $tmp .= "[STATICEND:$hash]";

            $output = $tmp;
        } else {
            $this->newStatics[] = $hash;

            foreach ($this->dynamicsByCurrentStackIdx[$currentStackIdx] ?? [] as $slot) {
                $output = (string) str($output)->replaceFirst($slot,
                    Utils::insertAttributesIntoHtmlRoot($slot, ['wire:dynamic' => $hash], strict: false)
                );
            }

            unset($this->dynamicsByCurrentStackIdx[$currentStackIdx]);

            $output = Utils::insertAttributesIntoHtmlRoot($output, [
                'wire:static' => $hash,
            ]);

            $output = (string) str($output)->replace($key, $hash);
        }

        $this->lastStaticContent = $output;

        return $output;
    }

    protected $dynamicsByCurrentStackIdx = [];

    public function startDynamic()
    {
        ob_start();
    }

    public function endDynamic()
    {
        $key = last($this->staticStack);
        $currentStackIdx = array_key_last($this->staticStack);

        if (! isset($this->dynamicsByCurrentStackIdx[$currentStackIdx])) {
            $this->dynamicsByCurrentStackIdx[$currentStackIdx] = [];
        }

        $output = ob_get_clean();

        $this->dynamicsByCurrentStackIdx[$currentStackIdx][] = $output;

        return $output;
    }

    public function shouldBypassStatic($hash)
    {
        return in_array($hash, $this->previousStatics);
    }

    public function getAllStatics()
    {
        return array_merge($this->previousStatics, $this->newStatics);
    }

    public function getNewStatics()
    {
        return $this->newStatics;
    }

    public function getRenderedStatics()
    {
        return $this->renderedStatics;
    }

    public function generateHashOfStatic($output, $currentStackIdx)
    {
        if ($this->lastStaticContent) {
            $output = (string) str($output)->replaceFirst($this->lastStaticContent, '');
        }

        $slots = $this->dynamicsByCurrentStackIdx[$currentStackIdx] ?? [];

        foreach ($slots as $slot) {
            $output = (string) str($output)->replaceFirst($slot, '');
        }

        return crc32($output);
    }
}
