<?php

namespace Livewire\Features\SupportEvents;

use function Livewire\store;
use Illuminate\Contracts\Queue\ShouldQueue;

trait HandlesEvents
{
    protected $listeners = [];

    protected function getListeners() {
        return $this->listeners;
    }

    public function dispatch($event, ...$params)
    {
        if(method_exists($event, 'dispatch')) {
            return $event->dispatch();
        }

        $event = new Event($event, $params);

        store($this)->push('dispatched', $event);

        return $event;
    }
}
