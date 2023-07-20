<?php

namespace Livewire\Features\SupportWireables;

use Livewire\ComponentHook;

class SupportWireables extends ComponentHook
{
    static function provide()
    {
        app('livewire')->propertySynthesizer(WireableSynth::class);
    }
}
