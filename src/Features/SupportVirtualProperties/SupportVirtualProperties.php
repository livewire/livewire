<?php

namespace Livewire\Features\SupportVirtualProperties;

use Livewire\Component;
use Livewire\ComponentHook;
use Livewire\Mechanisms\HandleSynths\HandleSynths;

use function Livewire\on;

class SupportVirtualProperties extends ComponentHook
{
    public static function provide()
    {
        on('virtual-property', function ($target, $name, $instance, $previous) {
            // Virtual properties on form objects aren't wire-addressable on
            // their own, so there's nothing to wire up for them...
            if (! $target instanceof Component) return;

            app(HandleSynths::class)->adopt($target, $name, $instance, $previous);
        });
    }

    // Construction stays lazy so a method can read state that mount() set, but
    // it can't be allowed to drift past the lifecycle: an instance that
    // registers attributes (a form object) would miss every hook it needs.
    // Materializing here — last hook of the phase — is late enough to see
    // sibling state and early enough that adoption still catches up...
    public function mount()
    {
        $this->component->initializeVirtualProperties();
    }

    // Hydration builds virtuals as it pours the snapshot into them, so this is
    // usually a no-op. It only bites for a property the snapshot predates —
    // a virtual method deployed between requests...
    public function hydrate()
    {
        $this->component->initializeVirtualProperties();
    }
}
