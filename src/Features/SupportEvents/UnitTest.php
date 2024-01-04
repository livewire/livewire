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

            #[BaseOn('bar')]
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
    public function listen_for_dynamic_event_name()
    {
        $component = Livewire::test(new class extends Component {
            public $post = ['id' => 2];

            public $foo = 'bar';

            #[BaseOn('bar.{post.id}')]
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

            #[BaseOn('bar')]
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
    public function it_can_register_multiple_listeners_via_attribute(): void
    {
        Livewire::test(new class extends Component {
            public $counter = 0;

            #[BaseOn('foo'), BaseOn('bar')]
            public function add(): void
            {
                $this->counter++;
            }

            public function render() { return '<div></div>'; }
        })
            ->dispatch('foo')
            ->assertSet('counter', 1)
            ->dispatch('bar')
            ->assertSet('counter', 2);
    }

    /** @test */
    public function it_can_register_multiple_listeners_via_attribute_userland(): void
    {
        Livewire::test(new class extends Component {
            public $counter = 0;

            #[\Livewire\Attributes\On('foo'), \Livewire\Attributes\On('bar')]
            public function add(): void
            {
                $this->counter++;
            }

            public function render() { return '<div></div>'; }
        })
            ->dispatch('foo')
            ->assertSet('counter', 1)
            ->dispatch('bar')
            ->assertSet('counter', 2);
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


    /** @test */
    public function receive_event_with_refresh_attribute()
    {
        $component = Livewire::test(ReceivesEventUsingRefreshAttribute::class);

        $this->assertEquals(1, ReceivesEventUsingRefreshAttribute::$counter);

        $component->dispatch('bar');

        $this->assertEquals(2, ReceivesEventUsingRefreshAttribute::$counter);
    }

    /** @test */
    public function it_can_register_multiple_listeners_via_refresh_attribute(): void
    {
        Livewire::test(ReceivesMultipleEventsUsingMultipleRefreshAttributes::class)
            ->tap(fn () => $this->assertEquals(1, ReceivesMultipleEventsUsingMultipleRefreshAttributes::$counter))
            ->dispatch('foo')
            ->tap(fn () => $this->assertEquals(2, ReceivesMultipleEventsUsingMultipleRefreshAttributes::$counter))
            ->dispatch('bar')
            ->tap(fn () => $this->assertEquals(3, ReceivesMultipleEventsUsingMultipleRefreshAttributes::$counter));
    }

    /** @test */
    public function it_can_register_multiple_listeners_via_single_refresh_attribute(): void
    {
        Livewire::test(ReceivesMultipleEventsUsingSingleRefreshAttribute::class)
            ->tap(fn () => $this->assertEquals(1, ReceivesMultipleEventsUsingSingleRefreshAttribute::$counter))
            ->dispatch('foo')
            ->tap(fn () => $this->assertEquals(2, ReceivesMultipleEventsUsingSingleRefreshAttribute::$counter))
            ->dispatch('bar')
            ->tap(fn () => $this->assertEquals(3, ReceivesMultipleEventsUsingSingleRefreshAttribute::$counter));
    }

    /** @test */
    public function it_can_register_multiple_listeners_via_refresh_attribute_userland(): void
    {
        Livewire::test(ReceivesMultipleEventsUsingMultipleUserlandRefreshAttributes::class)
            ->tap(fn () => $this->assertEquals(1, ReceivesMultipleEventsUsingMultipleUserlandRefreshAttributes::$counter))
            ->dispatch('foo')
            ->tap(fn () => $this->assertEquals(2, ReceivesMultipleEventsUsingMultipleUserlandRefreshAttributes::$counter))
            ->dispatch('bar')
            ->tap(fn () => $this->assertEquals(3, ReceivesMultipleEventsUsingMultipleUserlandRefreshAttributes::$counter));
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

#[BaseOn('bar')]
class ReceivesEventUsingRefreshAttribute extends Component
{
    public static $counter = 0;

    public function render() { static::$counter++; return '<div></div>'; }
}

#[BaseOn('foo'), BaseOn('bar')]
class ReceivesMultipleEventsUsingMultipleRefreshAttributes extends Component
{
    public static $counter = 0;

    public function render() { static::$counter++; return '<div></div>'; }
}

#[BaseOn(['foo', 'bar'])]
class ReceivesMultipleEventsUsingSingleRefreshAttribute extends Component
{
    public static $counter = 0;

    public function render() { static::$counter++; return '<div></div>'; }
}


#[\Livewire\Attributes\On('foo'), \Livewire\Attributes\On('bar')]
class ReceivesMultipleEventsUsingMultipleUserlandRefreshAttributes extends Component
{
    public static $counter = 0;

    public function render() { static::$counter++; return '<div></div>'; }
}
