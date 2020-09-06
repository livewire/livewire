<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireManager;

class ComponentEventsTest extends TestCase
{
    /** @test */
    public function receive_event()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->emit('bar', 'baz');

        $this->assertEquals($component->get('foo'), 'baz');
    }

    /** @test */
    public function receive_event_with_single_value_listener()
    {
        $component = Livewire::test(ReceivesEventsWithSingleValueListener::class);

        $component->emit('bar', 'baz');

        $this->assertEquals($component->get('foo'), 'baz');
    }

    /** @test */
    public function receive_event_with_multiple_parameters()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->emit('bar', 'baz', 'blab');

        $this->assertEquals($component->get('foo'), 'bazblab');
    }

    /** @test */
    public function listeners_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $this->assertTrue(in_array('bar', $component->payload['effects']['listeners']));
        $this->assertStringContainsString('bar', $component->payload['effects']['html']);
    }

    /** @test */
    public function server_emitted_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->runAction('emitGoo');

        $this->assertTrue(in_array(['event' => 'goo', 'params' => ['car']], $component->payload['effects']['emits']));
    }

    /** @test */
    public function server_emitted_up_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->runAction('emitUpGoo');

        $this->assertTrue(in_array(['ancestorsOnly' => true, 'event' => 'goo', 'params' => ['car']], $component->payload['effects']['emits']));
    }

    /** @test */
    public function server_emitted_self_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->runAction('emitSelfGoo');

        $this->assertTrue(in_array(['selfOnly' => true, 'event' => 'goo', 'params' => ['car']], $component->payload['effects']['emits']));
    }

    /** @test */
    public function server_emitted_to_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->runAction('emitToGooGone');

        $this->assertTrue(in_array(['to' => 'goo', 'event' => 'gone', 'params' => ['car']], $component->payload['effects']['emits']));
    }

    /** @test */
    public function server_dispatched_browser_events_are_provided_to_frontend()
    {
        $component = Livewire::test(DispatchesBrowserEvents::class);

        $component->runAction('dispatchFoo');

        // $this->assertTrue(in_array(['event' => 'foo', 'data' => ['bar' => 'baz']], $component->payload['effects']['dispatches']));
    }

    /** @test */
    public function component_can_set_dynamic_listeners()
    {
        $component = Livewire::test(ReceivesEventsWithDynamicListeners::class, ['listener' => 'bob']);

        $component->emit('bob', 'lob');
        $component->assertSet('foo', 'lob');
    }
}

class ReceivesEvents extends Component
{
    public $foo;

    protected $listeners = ['bar' => 'onBar'];

    public function onBar($value, $otherValue = '')
    {
        $this->foo = $value.$otherValue;
    }

    public function emitGoo()
    {
        $this->emit('goo', 'car');
    }

    public function emitUpGoo()
    {
        $this->emit('goo', 'car')->up();
    }

    public function emitSelfGoo()
    {
        $this->emit('goo', 'car')->self();
    }

    public function emitToGooGone()
    {
        $this->emit('gone', 'car')->to()->component('goo');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ReceivesEventsWithSingleValueListener extends Component
{
    public $foo;

    protected $listeners = ['bar'];

    public function bar($value)
    {
        $this->foo = $value;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ReceivesEventsWithDynamicListeners extends Component
{
    public $listener;
    public $foo = '';

    public function mount($listener)
    {
        $this->listener = $listener;
    }

    protected function getListeners() {
        return [$this->listener => 'handle'];
    }

    public function handle($value)
    {
        $this->foo = $value;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class DispatchesBrowserEvents extends Component
{
    public function dispatchFoo()
    {
        $this->dispatchBrowserEvent('foo', ['bar' => 'baz']);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
