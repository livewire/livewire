<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Event;
use Livewire\Livewire;
use Livewire\LivewireManager;

class ComponentEventInstanceTest extends TestCase
{
    /** @test */
    public function reieves_event_with_custom_class_name()
    {
        $component = Livewire::test(ReceivesCustomClassEvents::class);

        $component->emit(new CustomEvent('baz'));


        $this->assertEquals($component->get('foo'), 'baz');
    }
}

class CustomEvent extends Event
{
    public function __construct($param)
    {
        parent::__construct(self::class, compact('param'));
    }
}

class ReceivesCustomClassEvents extends Component
{
    public $foo;

    protected $listeners = [CustomEvent::class => 'onCustomEvent'];

    public function onCustomEvent($value)
    {
        $this->foo = $value;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
