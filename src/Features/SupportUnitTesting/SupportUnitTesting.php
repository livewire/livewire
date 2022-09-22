<?php

namespace Livewire\Features\SupportUnitTesting;

use Livewire\Synthesizers\LivewireSynth;
use Livewire\Mechanisms\ComponentDataStore;

class SupportUnitTesting
{
    function boot()
    {
        if (! app()->environment('testing')) return;

        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            return function () use ($context, $target) {
                ComponentDataStore::set($target, 'testing.html', $context->effects['html']);
            };
        });
    }
}
