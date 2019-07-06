<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Livewire\Connection\ComponentHydrator;

class ComponentEventsTest extends TestCase
{
    /** @test */
    function receive_event()
    {
        $component = app(LivewireManager::class)->test(ReceivesEvents::class);

        $component->fireEvent('bar', 'baz');

        $this->assertEquals($component->foo, 'baz');
    }

    /** @test */
    function listeners_are_provided_to_frontend()
    {
        $component = app(LivewireManager::class)->test(ReceivesEvents::class);

        $this->assertTrue(in_array('bar', $component->events));
        $this->assertContains('bar', $component->dom);
    }

    /** @test */
    function server_emitted_events_are_provided_to_frontend()
    {
        $component = app(LivewireManager::class)->test(ReceivesEvents::class);

        $component->runAction('emitGoo');

        $this->assertTrue(in_array(['event' => 'goo', 'params' => ['car']], $component->eventQueue));
    }
}

class ReceivesEvents extends Component {
    public $foo;

    protected $listeners = ['bar' => 'onBar'];

    public function onBar($value)
    {
        $this->foo = $value;
    }

    public function emitGoo()
    {
        $this->emit('goo', 'car');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
