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

            return function ($value) use ($context, $target) {
                ComponentDataStore::set($target, 'testing.html', $context->effects['html']);

                return $value;
            };
        });

        app('synthetic')->on('mount', function ($name, $params, $parent, $key, $slots, $hijack) {
            return function ($target) {
                return function ($html) use ($target) {
                    ComponentDataStore::set($target, 'testing.html', $html);
                };
            };
        });
    }
}
