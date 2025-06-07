<?php

namespace Livewire\V4\Slots;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

class SupportSlots extends ComponentHook
{
    public static function provide()
    {
        // Register the hook in IntegrateV4.php
    }

    public function render($view, $properties)
    {
        // Ensure slots are initialized
        $this->component->initializeSlots();

        // Get the slot object for the view
        $slotObject = $this->component->getSlotObjectForView();

        // Share the slot variables with views using the same pattern as validation errors
        $revert = Utils::shareWithViews('slot', $slotObject);

        return function () use ($revert) {
            // After the component has rendered, revert our global sharing
            $revert();
        };
    }
}
