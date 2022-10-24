<?php

namespace Livewire\Features\SupportWireables;

class SupportWireables
{
    function boot()
    {
        app('synthetic')->registerSynth(WireableSynth::class);
    }
}
