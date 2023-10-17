<?php

namespace Livewire\Features\SupportValidation;

use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

class SupportValidation extends ComponentHook
{
    public function hydrate($memo)
    {
        $this->component->setErrorBag(
            $memo['errors'] ?? []
        );
    }

    public function render($view, $data)
    {
        $errors = (new ViewErrorBag)->put('default', $this->component->getErrorBag());

        $revert = Utils::shareWithViews('errors', $errors);

        return function () use ($revert) {
            // After the component has rendered, let's revert our global
            // sharing of the "errors" variable with blade views...
            $revert();
        };
    }

    public function dehydrate($context)
    {
        $errors = $this->component->getErrorBag()->toArray();

        // Only persist errors that were born from properties on the component
        // and not from custom validators (Validator::make) that were run.
        $context->addMemo('errors', collect($errors)
            ->filter(function ($value, $key) {
                return Utils::hasProperty($this->component, $key);
            })
            ->toArray()
        );
    }

    public function exception($e, $stopPropagation)
    {
        if (! $e instanceof ValidationException) {
            return;
        }

        $this->component->setErrorBag($e->validator->errors());

        $stopPropagation();
    }
}
