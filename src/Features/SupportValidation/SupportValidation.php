<?php

namespace Livewire\Features\SupportValidation;

use function Livewire\on;
use function Livewire\invade;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\Drawer\Utils;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\MessageBag;
use Livewire\ComponentHook;

class SupportValidation extends ComponentHook
{
    function hydrate($meta)
    {
        $this->component->setErrorBag(
            $meta['errors'] ?? []
        );
    }

    function render($view, $data)
    {
        $errors = (new ViewErrorBag)->put('default', $this->component->getErrorBag());

        $revert = Utils::shareWithViews('errors', $errors);

        return function () use ($revert) {
            // After the component has rendered, let's revert our global
            // sharing of the "errors" variable with blade views...
            $revert();
        };
    }

    function dehydrate($context)
    {
        $errors = $this->component->getErrorBag()->toArray();

        // Only persist errors that were born from properties on the component
        // and not from custom validators (Validator::make) that were run.
        $context->addMeta('errors', collect($errors)
            ->filter(function ($value, $key) {
                return Utils::hasProperty($this->component, $key);
            })
            ->toArray()
        );
    }

    function exception($e, $stopPropagation)
    {
        if (! $e instanceof ValidationException) return;

        $this->component->setErrorBag($e->validator->errors());

        $stopPropagation();
    }
}
