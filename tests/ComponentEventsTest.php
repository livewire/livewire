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

    /** @test */
    function server_emitted_events_are_provided_to_frontend()
    {
        $component = app(LivewireManager::class)->test(ReceivesEvents::class);

        $component->runAction('emitGoo');

        $this->assertTrue(in_array(['event' => 'goo', 'params' => ['car']], $component->eventQueue));
    }

    /** @test */
    function manually_registered_events_are_provided_to_frontend()
    {
        $component = app(LivewireManager::class)->test(RegistersDynamicEvents::class);

        $this->assertTrue(in_array('foo', $component->listeningFor));
        $this->assertContains('foo', $component->dom);

        $this->assertTrue(in_array('echo:foo,bar', $component->listeningFor));
        $this->assertContains('echo:foo,bar', $component->dom);

        $this->assertTrue(in_array('echo-private:foo,bar', $component->listeningFor));
        $this->assertContains('echo-private:foo,bar', $component->dom);

        $this->assertTrue(in_array('echo-presence:foo,here', $component->listeningFor));
        $this->assertContains('echo-presence:foo,here', $component->dom);

        $this->assertTrue(in_array('echo-notification:foo', $component->listeningFor));
        $this->assertContains('echo-notification:foo', $component->dom);
    }
}

class ReceivesEvents extends LivewireComponent {
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

class RegistersDynamicEvents extends LivewireComponent {

    public function mount()
    {
        $this->registerListener('foo', 'onFoo');
        
        $this->registerEchoListener('foo','bar','onEchoFoo');

        $this->registerEchoPrivateListener('foo','bar','onEchoPrivateFoo');

        $this->registerEchoPresenceListener('foo','here','onEchoPresenceFoo');

        $this->registerEchoNotificationListener('foo','onEchoNotificationFoo');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
