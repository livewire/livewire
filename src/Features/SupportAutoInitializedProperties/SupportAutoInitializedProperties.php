<?php

namespace Livewire\Features\SupportAutoInitializedProperties;

use Livewire\ComponentHook;
use Livewire\Mechanisms\HandleSynths\HandleSynths;

/**
 * Typed public properties whose synthesizer knows how to initialize them
 * (an initialize() method on the synth) spring to life automatically:
 *
 *     public Selection $selection;   // no mount() assignment needed
 *
 * The machinery lives in the synth system (HandleSynths) — this feature
 * just hooks it into the component lifecycle.
 */
class SupportAutoInitializedProperties extends ComponentHook
{
    function boot()
    {
        app(HandleSynths::class)->initializeProperties($this->component);
    }
}
