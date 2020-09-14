<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;

class SupportValidation
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.dehydrate', function ($component, $response) {
            $response->memo['errors'] = collect(
                $component->getErrorBag()->toArray()
            )->filter(function ($value, $key) use ($component) {
                return $component->hasProperty($key);
            })->toArray();
        });

        Livewire::listen('component.hydrate', function ($component, $request) {
            $component->setErrorBag(
                $request->memo['errors'] ?? []
            );
        });
    }
}
