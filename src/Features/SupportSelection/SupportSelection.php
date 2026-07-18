<?php

namespace Livewire\Features\SupportSelection;

use Livewire\ComponentHook;

class SupportSelection extends ComponentHook
{
    public static function provide()
    {
        app('livewire')->propertySynthesizer(
            SelectionSynth::class
        );
    }
}
