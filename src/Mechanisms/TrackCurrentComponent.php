<?php

namespace Livewire\Mechanisms;

use function Synthetic\on;
use Livewire\LivewireSynth;

class TrackCurrentComponent
{
    public function boot()
    {
        on('mount', function () {
            return function ($component) {
                app('livewire')->setCurrent($component);
            };
        });

        on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($component) {
                app('livewire')->setCurrent($component);
            };
        });
    }
}
