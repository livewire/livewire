<?php

namespace Livewire\Features\SupportModels;

use function Synthetic\on;

/**
 * Depends on: SupportValidation for ->missingRuleFor() method on component. (inside ModelSynth)
 */
class SupportModels
{
    function boot()
    {
        app('synthetic')->registerSynth([
            ModelSynth::class,
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
