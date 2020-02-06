<?php

namespace Livewire\ComponentConcerns;

trait ReceivesEvents
{
    protected $eventQueue = [];
    protected $dispatchQueue = [];
    protected $listeners = [];

    protected function getListeners() {
        return $this->listeners;
    }

    public function emit($event, ...$params)
    {
        $this->eventQueue[] = [
            'event' => $event,
            'params' => $params,
        ];
    }

    public function emitUp($event, ...$params)
    {
        $this->eventQueue[] = [
            'event' => $event,
            'params' => $params,
            'ancestorsOnly' => true,
        ];
    }

    public function dispatchBrowserEvent($event, $data = null)
    {
        $this->dispatchQueue[] = [
            'event' => $event,
            'data' => $data,
        ];
    }

    public function getEventQueue()
    {
        return $this->eventQueue;
    }

    public function getDispatchQueue()
    {
        return $this->dispatchQueue;
    }

    protected function getEventsAndHandlers()
    {
        return collect($this->getListeners())
            ->mapWithKeys(function ($value, $key) {
                $key = is_numeric($key) ? $value : $key;

                return [$key => $value];
            })->toArray();
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
