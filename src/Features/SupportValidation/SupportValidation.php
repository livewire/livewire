<?php

namespace Livewire\Features\SupportValidation;

use function Livewire\invade;
use function Synthetic\on;
use Livewire\Synthesizers\LivewireSynth;
use Livewire\Mechanisms\ComponentDataStore;
use Livewire\Drawer\Utils;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\MessageBag;

class SupportValidation
{
    function boot()
    {
        on('exception', function ($target, $e, $stopPropagation) {
            if (! $target instanceof Component) return;
            if (! $e instanceof ValidationException) return;

            $target->setErrorBag($e->validator->errors());

            $stopPropagation();
        });

        on('render', function ($target, $view, $data) {
            $errors = (new ViewErrorBag)->put('default', $target->getErrorBag());

            $revert = Utils::shareWithViews('errors', $errors);

            return function () use ($revert) {
                // After the component has rendered, let's revert our global
                // sharing of the "errors" variable with blade views...
                $revert();
            };
        });

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $errors = $target->getErrorBag()->toArray();

            // Only persist errors that were born from properties on the component
            // and not from custom validators (Validator::make) that were run.
            $context->addMeta('errors', collect($errors)
                ->filter(function ($value, $key) use ($target) {
                    return Utils::hasProperty($target, $key);
                })
                ->toArray()
            );
        });

        on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($target) use ($meta) {
                $target->setErrorBag(
                    $meta['errors'] ?? []
                );
            };
        });
    }
}
