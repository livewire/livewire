<?php

namespace Tests;

use Livewire\LivewireManager;
use Livewire\Component;

class TestableLiveWireEmitEventsTest extends TestCase
{
    /** @test */
    public function receive_event_with_single_value()
    {

        $component = app(LivewireManager::class)->test(ReceivesEventsEmitWithSingleValueListener::class);

        $component->emit('bar', 'baz');

        $this->assertEquals($component->get('foo'), 'baz');
    }
}

class ReceivesEventsEmitWithSingleValueListener extends Component
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
