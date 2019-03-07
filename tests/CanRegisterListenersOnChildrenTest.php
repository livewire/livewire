<?php

namespace Tests;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Livewire\LivewireComponent;
use Livewire\LivewireManager;
use Illuminate\View\Factory;
use Livewire\LivewireComponentWrapper;
use CalebPorzio\GitDown;

class CanRegisterListenersOnChildrenTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        app('livewire')->component('dummy', ParentWithListenersStub::class);
        app('livewire')->component('dummy-child', ChildWithEventsStub::class);
    }

    /** @test */
    function parent_component_registers_listeners_for_children()
    {
        $component = LivewireComponentWrapper::wrap(app('livewire')->activate('dummy'));

        $component->output();

        $this->assertEquals('someAction', head($component->listeners())['someEvent']);
    }

    /** @test */
    function child_components_fired_event_gets_picked_up_by_parent()
    {
        $this->expectException(MethodOnParentComponentWasCalled::class);

        $component = LivewireComponentWrapper::wrap(app('livewire')->activate('dummy'));

        $component->output();

        $response = $this->withoutExceptionHandling()->post('/livewire/message', [
            'id' => $component->id,
            'type' => 'fireEvent',
            'data' => [
                'name' => 'someEvent',
                'childId' => head($component->children),
                'params' => [],
            ],
            'serialized' => encrypt($component->wrapped),
        ]);
    }
}

class MethodOnParentComponentWasCalled extends \Exception {}

class ParentWithListenersStub extends LivewireComponent {
    public function someAction()
    {
        throw new MethodOnParentComponentWasCalled;
    }

    public function render()
    {
        return app('view')->make('child-event-listener-test');
    }
}

class ChildWithEventsStub extends LivewireComponent {
    public function fireEvent()
    {
        $this->emit('someEvent');
    }

    public function render()
    {
        return app('view')->make('id-test');
    }
}
