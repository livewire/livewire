<?php

namespace Livewire\Features\SupportWireables;

class SupportWireables
{
    function boot()
    {
        app('livewire')->synth(WireableSynth::class);
    }
}
