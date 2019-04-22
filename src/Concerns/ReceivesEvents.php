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

    protected function getEventsAndHandlers()
    {
        if (method_exists($this, 'listeners')) {
            return $this->listeners();
        }
        return $this->listeners ?? [];
    }

    public function getEventsBeingListenedFor()
    {
        return array_keys($this->getEventsAndHandlers());
    }

    public function fireEvent($event, $params)
    {
        $method = $this->getEventsAndHandlers()[$event];

        $this->callMethod($method, $params);
    }
}
