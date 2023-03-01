<?php

namespace Livewire\Features\SupportModels;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportModels extends ComponentHook
{
    static function provide()
    {
        app('livewire')->synth([
            ModelSynth::class,
        ]);
    }
}
