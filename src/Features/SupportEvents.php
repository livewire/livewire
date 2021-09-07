<?php

namespace Livewire\Features;

use Livewire\Livewire;

class SupportEvents
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.hydrate', function ($component, $request) {
            //
        });

        Livewire::listen('component.dehydrate.initial', function ($component, $response) {
            $response->effects['listeners'] = $component->getEventsBeingListenedFor();
        });

        Livewire::listen('component.dehydrate', function ($component, $response) {
            $emits = $component->getEventQueue();
            $dispatches = $component->getDispatchQueue();

            if ($emits) {
                $response->effects['emits'] = $emits;
            }

            if ($dispatches) {
                $response->effects['dispatches'] = $dispatches;
            }
        });
    }
}
