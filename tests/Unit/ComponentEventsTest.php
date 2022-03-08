<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

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
    public function emit_dynamically_deleted_event_generates_error()
    {
        $component = Livewire::test(ReceivesEventsWithModifiedDynamicListeners::class);

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Undefined array key "bar"');

        $component->call('delete', 2)
                  ->emit('bar', 'bar');
    }

    /** @test */
    public function emit_dynamically_added_event()
    {
        $component = Livewire::test(ReceivesEventsWithModifiedDynamicListeners::class);

        $component->call('add', 4, 'goo')
                  ->emit('goo', 'goo')
                  ->assertSet('lastEvent', 'goo');
    }

    /** @test */
    public function dynamically_added_listeners_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEventsWithModifiedDynamicListeners::class);

        $this->assertTrue(in_array('bar', $component->payload['effects']['listeners']));

        $component->call('add', 4, 'goo');
        $this->assertTrue(isset($component->payload['effects']['listeners']));
        $this->assertTrue(is_array($component->payload['effects']['listeners']));
        $this->assertTrue(in_array('goo', $component->payload['effects']['listeners']));
    }

    /** @test */
    public function dynamically_removed_listeners_are_removed_on_frontend()
    {
        $component = Livewire::test(ReceivesEventsWithModifiedDynamicListeners::class);

        $this->assertTrue(in_array('bar', $component->payload['effects']['listeners']));

        $component->call('delete', 2);

        $this->assertTrue(isset($component->payload['effects']['listeners']));
        $this->assertTrue(is_array($component->payload['effects']['listeners']));
        $this->assertFalse(in_array('bar', $component->payload['effects']['listeners']));
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

        $component->call('emitGoo');

        $this->assertTrue(in_array(['event' => 'goo', 'params' => ['car']], $component->payload['effects']['emits']));
    }

    /** @test */
    public function server_emitted_up_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->call('emitUpGoo');

        $this->assertTrue(in_array(['ancestorsOnly' => true, 'event' => 'goo', 'params' => ['car']], $component->payload['effects']['emits']));
    }

    /** @test */
    public function server_emitted_self_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->call('emitSelfGoo');

        $this->assertTrue(in_array(['selfOnly' => true, 'event' => 'goo', 'params' => ['car']], $component->payload['effects']['emits']));
    }

    /** @test */
    public function server_emitted_to_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->call('emitToGooGone');

        $this->assertTrue(in_array(['to' => 'goo', 'event' => 'gone', 'params' => ['car']], $component->payload['effects']['emits']));
    }

    /** @test */
    public function server_dispatched_browser_events_are_provided_to_frontend()
    {
        Livewire::test(DispatchesBrowserEvents::class)
                ->call('dispatchFoo')
                ->assertDispatchedBrowserEvent('foo', ['bar' => 'baz']);
    }

    /** @test */
    public function component_can_set_dynamic_listeners()
    {
        Livewire::test(ReceivesEventsWithDynamicListeners::class, ['listener' => 'bob'])
                ->emit('bob', 'lob')
                ->assertSet('foo', 'lob');
    }

    /** @test */
    public function component_receives_events_emitted_using_classname()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->call('emitToComponentUsingClassname');

        $this->assertTrue(in_array(['to' => 'tests.unit.it-can-receive-event-using-classname', 'event' => 'foo', 'params' => ['test']], $component->payload['effects']['emits']));
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
        $this->emitUp('goo', 'car');
    }

    public function emitSelfGoo()
    {
        $this->emitSelf('goo', 'car');
    }

    public function emitToGooGone()
    {
        $this->emitTo('goo', 'gone', 'car');
    }

    public function emitToComponentUsingClassname()
    {
        $this->emitTo(ItCanReceiveEventUsingClassname::class, 'foo', 'test');
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

class ReceivesEventsWithModifiedDynamicListeners extends Component
{
    public $lastEvent = '';

    public $eventsToListenFor = [
        1 => 'foo',
        2 => 'bar',
        3 => 'baz',
    ];

    public function handle($event)
    {
        $this->lastEvent = $event;
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

class ItCanReceiveEventUsingClassname extends Component {

    public $bar;

    public $listeners = [
        'foo' => 'bar'
    ];

    public function onBar($value)
    {
        $this->bar = $value;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
