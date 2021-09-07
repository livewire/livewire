<?php

namespace Livewire\Features;

use Livewire\Livewire;
use Illuminate\Support\Facades\App;

class SupportLocales
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.dehydrate.initial', function ($component, $response) {
            $response->fingerprint['locale'] = app()->getLocale();
        });

        Livewire::listen('component.hydrate.subsequent', function ($component, $request) {
           if ($locale = $request->fingerprint['locale']) {
                App::setLocale($locale);
            }
        });
    }
}
