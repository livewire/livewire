<?php

namespace Livewire\Features\SupportModels;

use Livewire\ComponentHook;

class SupportModels extends ComponentHook
{
    public static function provide()
    {
        app('livewire')->propertySynthesizer([
            ModelSynth::class,
            EloquentCollectionSynth::class,
        ]);
    }
}
