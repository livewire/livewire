<?php

namespace Tests;

use Livewire\LivewireComponent;
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

        $this->assertTrue(in_array('bar', $component->listeningFor));
        $this->assertContains('bar', $component->dom);
    }
}

class ReceivesEvents extends LivewireComponent {
    public $foo;

    protected $listeners = ['bar' => 'onBar'];

    public function onBar($value)
    {
        $this->foo = $value;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
