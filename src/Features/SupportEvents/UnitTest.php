<?php

namespace Livewire\Features\SupportEvents;

use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function receive_event_with_attribute()
    {
        $component = Livewire::test(new class extends Component {
            public $foo = 'bar';

            #[On('bar')]
            public function onBar($param)
            {
                $this->foo = $param;
            }

            public function render() { return '<div></div>'; }
        });

        $component->dispatch('bar', 'baz');

        $this->assertEquals($component->get('foo'), 'baz');
    }

    /** @test */
    public function receive_event_with_attribute_from_children()
    {
        $component = Livewire::test(new class extends Component {
            public $foo = 'bar';

            #[On('bar', fromChildren: true)]
            public function onBar($param)
            {
                $this->foo = $param;
            }

            public function render()
            {
                return '<div></div>';
            }
        });

        $component->dispatch('bar', 'baz');

        $this->assertEquals($component->get('foo'), 'baz');
    }

    /** @test */
    public function listen_for_dynamic_event_name()
    {
        $component = Livewire::test(new class extends Component {
            public $post = ['id' => 2];

            public $foo = 'bar';

            #[On('bar.{post.id}')]
            public function onBar($param)
            {
                $this->foo = $param;
            }

            public function render() { return '<div></div>'; }
        });

        $component->dispatch('bar.2', 'baz');

        $this->assertEquals($component->get('foo'), 'baz');
    }

    /** @test */
    public function listens_for_event_with_named_params()
    {
        $component = Livewire::test(new class extends Component {
            public $foo = 'bar';

            #[On('bar')]
            public function onBar($name, $game)
            {
                $this->foo = $name . $game;
            }

            public function render() { return '<div></div>'; }
        });

        $component->dispatch('bar', game: 'shmaz', name: 'baz');

        $this->assertEquals($component->get('foo'), 'bazshmaz');
    }

    /** @test */
    public function dispatches_event_with_named_params()
    {
        Livewire::test(new class extends Component {
            public function dispatchFoo()
            {
                $this->dispatch('foo', name: 'bar', game: 'baz');
            }

            public function render() { return '<div></div>'; }
        })
            ->call('dispatchFoo')
            ->assertDispatched('foo')
            ->assertDispatched('foo', name: 'bar')
            ->assertDispatched('foo', name: 'bar', game: 'baz')
            ->assertDispatched('foo', game: 'baz', name: 'bar')
            ->assertNotDispatched('foo', games: 'baz')
            ->assertNotDispatched('foo', name: 'baz')
        ;
    }

    /** @test */
    public function receive_event()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->dispatch('bar', 'baz');

        $this->assertEquals($component->get('foo'), 'baz');
    }

    /** @test */
    public function receive_event_with_single_value_listener()
    {
        $component = Livewire::test(ReceivesEventsWithSingleValueListener::class);

        $component->dispatch('bar', 'baz');

        $this->assertEquals($component->get('foo'), 'baz');
    }

    /** @test */
    public function receive_event_with_multiple_parameters()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->dispatch('bar', 'baz', 'blab');

        $this->assertEquals($component->get('foo'), 'bazblab');
    }

    /** @test */
    public function listeners_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $this->assertTrue(in_array('bar', $component->effects['listeners']));
    }

    /** @test */
    public function server_dispatched_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->call('dispatchGoo');

        $this->assertTrue(in_array(['name' => 'goo', 'params' => ['car']], $component->effects['dispatches']));
    }

    /** @test */
    public function server_dispatched_self_events_are_provided_to_frontend()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->call('dispatchSelfGoo');

        $this->assertTrue(in_array(['self' => true, 'name' => 'goo', 'params' => ['car']], $component->effects['dispatches']));
    }

    /** @test */
    public function component_can_set_dynamic_listeners()
    {
        Livewire::test(ReceivesEventsWithDynamicListeners::class, ['listener' => 'bob'])
                ->dispatch('bob', 'lob')
                ->assertSet('foo', 'lob');
    }

    /** @test */
    public function component_receives_events_dispatched_using_classname()
    {
        $component = Livewire::test(ReceivesEvents::class);

        $component->call('dispatchToComponentUsingClassname');

        $this->assertTrue(in_array(['to' => 'livewire.features.support-events.it-can-receive-event-using-classname', 'name' => 'foo', 'params' => ['test']], $component->effects['dispatches']));
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

    public function dispatchGoo()
    {
        $this->dispatch('goo', 'car');
    }

    public function dispatchSelfGoo()
    {
        $this->dispatch('goo', 'car')->self();
    }

    public function dispatchToGooGone()
    {
        $this->dispatch('gone', 'car')->to('goo');
    }

    public function dispatchToComponentUsingClassname()
    {
        $this->dispatch('foo', 'test')->to(ItCanReceiveEventUsingClassname::class);
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

    protected function getListeners()
    {
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

class ItCanReceiveEventUsingClassname extends Component
{
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
