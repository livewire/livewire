<?php

namespace Livewire\Features\SupportStaticPartials;

use Livewire\Drawer\Utils;

trait HandlesStaticPartials
{
    protected $previousStatics = [];
    protected $newStatics = [];
    protected $staticStack = [];
    protected $renderedStatics = [];

    public function setPreviousStatics($statics)
    {
        $this->previousStatics = $statics;
    }

    public function startStatic($key)
    {
        $this->staticStack[] = $key;

        if ($this->shouldProcessStatic($key)) {
            // It's important to add "newStatics" in "startStatic" rather than
            // "endStatic" so that their order matches document.querySelectorAll()
            // when nesting the same component within itself...
            $this->newStatics[] = $key;
        }

        ob_start();
    }

    public function endStatic()
    {
        $currentStackIdx = array_key_last($this->staticStack);
        $key = array_pop($this->staticStack);

        $output = ob_get_clean();

        if ($this->shouldProcessStatic($key)) {
            $output = Utils::insertAttributesIntoHtmlRoot($output, [
                'wire:static' => $key,
            ]);

            return $output;
        } else {
            // It's important to add "renderedStatics" in "endStattic" rather than
            // "startStatic" so that the order is "depth-first". This allows
            // JavaScript to recursively regex easily and reliably...
            $this->renderedStatics[] = $key;

            $tmp = "[STATICSTART:$key]";

            foreach ($this->staticSlotsByCurrentStackIdx[$currentStackIdx] ?? [] as $slot) {
                $tmp .= "[STATICSLOTSTART:$key]{$slot}[STATICSLOTEND:$key]";
            }

            unset($this->staticSlotsByCurrentStackIdx[$currentStackIdx]);

            $tmp .= "[STATICEND:$key]";

            return $tmp;
        }
    }

    protected $staticSlotsByKey = [];
    protected $staticSlotsByCurrentStackIdx = [];

    public function startStaticSlot()
    {
        ob_start();
    }

    public function endStaticSlot()
    {
        $key = last($this->staticStack);
        $currentStackIdx = array_key_last($this->staticStack);

        if (! isset($this->staticSlotsByCurrentStackIdx[$currentStackIdx])) {
            $this->staticSlotsByCurrentStackIdx[$currentStackIdx] = [];
        }

        $output = ob_get_clean();

        if ($this->shouldProcessStatic($key)) {
            $output = Utils::insertAttributesIntoHtmlRoot($output, [
                'wire:static-slot' => $key,
            ]);

            return $output;
        } else {
            $this->staticSlotsByCurrentStackIdx[$currentStackIdx][] = $output;
        }
    }

    public function shouldProcessStatic($key)
    {
        return ! in_array($key, $this->previousStatics);
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
}
