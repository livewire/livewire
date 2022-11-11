<?php

namespace Livewire\Features\SupportModels;

use function Livewire\on;

/**
 * Depends on: SupportValidation for ->missingRuleFor() method on component. (inside ModelSynth)
 */
class SupportModels
{
    function boot()
    {
        app('livewire')->synth([
            ModelSynth::class,
            CastableSynth::class,
        ]);

        on('update', function ($target, $path, $value) {
            if (! $target instanceof \Livewire\Component) return;

            // dd($path, $value);
            // if ()
        });

        // on('update', function ($component, ) {
        //     Livewire\Exceptions\CannotBindToModelDataWithoutValidationRuleException
        // });
    }
}
