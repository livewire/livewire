<?php

namespace Livewire\Concerns;

trait ReceivesEvents
{
    protected $eventQueue = [];

    public function emit($event, ...$params)
    {
        $this->eventQueue[] = [
            'event' => $event,
            'params' => $params,
        ];
    }

    public function getEventQueue()
    {
        return $this->eventQueue;
    }

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
