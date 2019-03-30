<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;

class DataBindingTest extends TestCase
{
    /** @test */
    function update_component_data()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals('bar', $component->instance->foo);
    }

    /** @test */
    function update_nested_component_data_inside_array()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->updateProperty('foo', []);
        $component->updateProperty('foo.0', 'bar');
        $component->updateProperty('foo.bar', 'baz');

        $this->assertEquals(['bar', 'bar' => 'baz'], $component->instance->foo);
    }

    /** @test */
    function property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals('bar', $component->instance->foo);
        $this->assertEmpty($component->dirtyInputs);

        $component->runAction('changeFoo', 'baz');

        $this->assertEquals('baz', $component->instance->foo);
        $this->assertContains('foo', $component->dirtyInputs);
    }

    /** @test */
    function lazy_synced_data_doesnt_shows_up_as_dirty()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component
            ->queueLazilyUpdateProperty('foo', 'bar')
            ->runAction('$refresh');

        $this->assertEmpty($component->dirtyInputs);
    }
}

class DataBindingStub extends LivewireComponent {
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
