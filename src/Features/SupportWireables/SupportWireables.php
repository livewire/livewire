<?php

namespace Livewire\Features\SupportWireables;

class SupportWireables
{
    function boot()
    {
        // app('synthetic')->registerSynth(WireableSynth::class);

        // @todo: problem...
        // I can't easily create a synth for wireablles because they are hydreated
        // based on the typehint of the property and currently the hydrate hooks
        // on synthesizers don't give access to that because of the order in which
        // things are hydreated (furthest nested thing, out...)
    }
}
