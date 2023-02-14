<?php

namespace Livewire\Features\SupportEvents;

use function Livewire\store;

trait HandlesEvents
{
    protected $listeners = [];

    protected function getListeners() {
        return $this->listeners;
    }

    public function emit($event, ...$params)
    {
        $event = new Event($event, $params);

        store($this)->push('emitted', $event);

        return $event;
    }

    public function emitUp($event, ...$params)
    {
        $this->emit($event, ...$params)->up();
    }

    public function emitSelf($event, ...$params)
    {
        $this->emit($event, ...$params)->self();
    }

    public function emitTo($name, $event, ...$params)
    {
        $this->emit($event, ...$params)->component($name);
    }

    public function dispatchBrowserEvent($event, $data = null)
    {
        store($this)->push('dispatched', [
            'event' => $event,
            'data' => $data,
        ]);
    }
}
