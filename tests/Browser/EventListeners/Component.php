<?php

namespace Tests\Browser\EventListeners;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $lastEvent = '';
    public $eventCount = 0;
    public $eventsToListenFor = [
        1 => 'foo',
        2 => 'bar',
        3 => 'baz',
    ];

    public function handle($event)
    {
        $this->lastEvent = $event;
        ++$this->eventCount;
    }

    public function delete($id)
    {
        unset($this->eventsToListenFor[$id]);
    }

    public function add($id, $event)
    {
        $this->eventsToListenFor[$id] = $event;
    }

    protected function getListeners()
    {
        return collect($this->eventsToListenFor)
                ->flip()
                ->map(function($item) { return 'handle'; });
    }

    public function render()
    {
        return <<<'BLADE'
    <div>
        <button dusk="foo" wire:click="$emit('foo', 'foo')">Foo</button>
        <button dusk="bar" wire:click="$emit('bar', 'bar')">Bar</button>
        <button dusk="baz" wire:click="$emit('baz', 'baz')">Baz</button>
        <button dusk="goo" wire:click="$emit('goo', 'goo')">Goo</button><br />

        <span dusk="eventCount">{{$eventCount}}</span><br />
        <span dusk="lastEvent">{{$lastEvent}}</span><br />

        <button dusk="remove2" wire:click="delete(2)">Remove bar handler</button><br />
        <button dusk="add4" wire:click="add(4, 'goo')">Add goo handler</button>
    </div>
BLADE;

    }
}
