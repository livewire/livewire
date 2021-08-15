<?php

namespace Livewire\Features;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Livewire\Livewire;

class SupportCollections
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('property.dehydrate', function ($name, $value, $component, $response) {
            if (! $value instanceof Collection || $value instanceof EloquentCollection) return;


        });

        Livewire::listen('property.hydrate', function ($name, $value, $component, $request) {
            $collections = data_get($request->memo, 'dataMeta.collections', []);

            foreach ($collections as $name) {
                data_set($component, $name, collect(data_get($component, $name)));
            }
        });
    }
}
