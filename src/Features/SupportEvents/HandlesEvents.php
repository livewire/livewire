<?php

namespace Livewire\Features\SupportEvents;

use function Livewire\store;

trait HandlesEvents
{
    /** @var array<string> */
    protected $listeners = [];

    /**
     * @return array<string>
     */
    protected function getListeners() {
        return $this->listeners;
    }

    /**
     * @param string $event 
     * @param mixed... $params 
     *
     * @return Event 
     */
    public function dispatch($event, ...$params)
    {
        $event = new Event($event, $params);

        store($this)->push('dispatched', $event);

        return $event;
    }
}
