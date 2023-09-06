<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\Livewire;
use Livewire\ComponentHook;

class SupportMorphAwareIfStatement extends ComponentHook
{
    static function provide()
    {
        if (! config('livewire.inject_morph_markers', true)) return;

        Livewire::precompiler(function ($entire) {
            return (new InjectMarkers)->inject($entire);
        });
    }
}
