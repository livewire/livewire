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

    public function registerListener($event, $method)
    {
        $listeners = $this->listeners ?? [];

        $listeners[$event] = $method;

        $this->listeners = $listeners;
    }
    
    public function registerEchoListener($channel, $event, $method){
        $this->registerListener('echo:' . $channel . ',' . $event, $method);
    }
    
    public function registerEchoPrivateListener($channel, $event, $method){
        $this->registerListener('echo-private:' . $channel . ',' . $event, $method);
    }
    
    public function registerEchoPresenceListener($channel, $event, $method){
        $this->registerListener('echo-presence:' . $channel . ',' . $event, $method);
    }
}
