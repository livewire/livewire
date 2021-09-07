<?php

namespace Livewire\Features;

use Livewire\Livewire;

class SupportValidation
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.dehydrate', function ($component, $response) {
            $errors = $component->getErrorBag()->toArray();

            // Only persist errors that were born from properties on the component
            // and not from custom validators (Validator::make) that were run.
            $response->memo['errors'] = collect($errors)
                ->filter(function ($value, $key) use ($component) {
                    return $component->hasProperty($key);
                })
                ->toArray();
        });

        Livewire::listen('component.hydrate', function ($component, $request) {
            $component->setErrorBag(
                $request->memo['errors'] ?? []
            );
        });
    }
}
