<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;

class SupportQueryString
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.hydrate.initial', function ($component, $request) {
            if (empty($properties = $component->getFromQueryStringProperties())) return;

            foreach ($properties as $property) {
                $component->$property = request()->query($property, $component->$property);
            }
        });

        Livewire::listen('component.dehydrate.initial', function ($component, $response) {
            if (empty($component->getFromQueryStringProperties())) return;

            $response->effects['query'] = [
                'properties' => $component->getFromQueryStringProperties(),
                'excepts' => $component->getFromQueryStringExcepts(),
            ];
        });
    }
}
