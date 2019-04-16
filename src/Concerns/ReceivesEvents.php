<?php

namespace Livewire\Concerns;

trait ReceivesEvents
{
    public function fireEvent($event, $params)
    {
        $method = $this->listeners[$event];

        $this->{$method}(...$params);
    }
}
