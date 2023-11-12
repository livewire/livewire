<?php

namespace Livewire\Features\SupportEnums;

use Livewire\ComponentHook;

class SupportEnums extends ComponentHook
{
    public static function provide()
    {
        app('livewire')->propertySynthesizer([
            EnumSynth::class,
        ]);
    }
}
