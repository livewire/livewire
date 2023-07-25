<?php

namespace Livewire\Features\SupportModels;

use Livewire\ComponentHook;

class SupportModels extends ComponentHook
{
    static function provide()
    {
        app('livewire')->propertySynthesizer([
            ModelSynth::class,
        ]);
    }
}
