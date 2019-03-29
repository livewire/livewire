<?php

namespace Tests;

use Livewire\Connection\ComponentHydrator;
use Livewire\Connection\TestConnectionHandler;
use Livewire\LivewireComponent;
use Livewire\LivewireComponentWrapper;
use Livewire\LivewireManager;

class DataBindingTest extends TestCase
{
    /** @test */
    function set_component_data()
    {
        $component = app(LivewireManager::class)->test(FaucetStub::class);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals('bar', $component->instance->foo);
    }

    /** @test */
    function property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action()
    {
        $component = app(LivewireManager::class)->test(FaucetStub::class);

        $component->updateProperty('foo', 'bar');

        $this->assertequals('bar', $component->instance->foo);

        $component->runAction('changeFoo', 'baz');

        $this->assertequals('baz', $component->instance->foo);
        $this->assertContains('foo', $component->instance->dirtyInputs());
    }

    /** @test */
    function lazy_synced_data_doesnt_shows_up_as_dirty()
    {
        $component = new FaucetStub('id', $prefix = 'wire');

        $instance = LivewireComponentWrapper::wrap($component);
        $instance->lazySyncInput('modelNumber', '123abc');
        $this->assertEmpty($instance->dirtyInputs());
    }
}

class FaucetStub extends LivewireComponent {
    public $foo;

    public function changeFoo($value)
    {
        $this->foo = $value;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
