<?php

namespace Livewire\Concerns;

trait ReceivesEvents
{
    public function getEventsBeingListenedFor()
    {
        return array_keys($this->listeners ?? []);
    }

    public function fireEvent($event, $params)
    {
        $method = $this->listeners[$event];

        $this->{$method}(...$params);
    }
}
