<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Illuminate\Routing\UrlGenerator;

class DataBindingTest extends TestCase
{
    /** @test */
    public function update_component_data()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals('bar', $component->instance->foo);
    }

    /** @test */
    public function update_nested_component_data_inside_array()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->updateProperty('foo', []);
        $component->updateProperty('foo.0', 'bar');
        $component->updateProperty('foo.bar', 'baz');

        $this->assertEquals(['bar', 'bar' => 'baz'], $component->instance->foo);
    }

    /** @test */
    public function property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action()
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
    public function nested_property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->updateProperty('arrayProperty.1', 'baz');

        $this->assertEquals(['foo', 'baz'], $component->instance->arrayProperty);
        $this->assertEmpty($component->dirtyInputs);

        $component->runAction('changeArrayPropertyOne', 'bar');

        $this->assertEquals(['foo', 'bar'], $component->instance->arrayProperty);
        $this->assertContains('arrayProperty.1', $component->dirtyInputs);
    }

    /** @test */
    public function nested_property_is_marked_as_dirty_if_removed_as_side_effect_of_an_action()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->runAction('removeArrayPropertyOne');

        $this->assertEquals(['foo'], $component->instance->arrayProperty);
        $this->assertContains('arrayProperty.1', $component->dirtyInputs);
    }

    /** @test */
    public function property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action_even_if_the_action_is_data_binding_for_that_specific_property()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->updateProperty('propertyWithHook', 'something');

        $this->assertEquals('something else', $component->instance->propertyWithHook);
        $this->assertContains('propertyWithHook', $component->dirtyInputs);
    }

    /** @test */
    public function component_actions_get_automatic_dependancy_injection()
    {
        $component = app(LivewireManager::class)->test(DataBindingStub::class);

        $component->runAction('hasInjections', 'foobar');

        $this->assertEquals('http://localhost', $component->foo);
        $this->assertEquals('foobar', $component->bar);
    }
}

class DataBindingStub extends Component
{
    public $foo;
    public $bar;
    public $propertyWithHook;
    public $arrayProperty = ['foo', 'bar'];

    public function updatedPropertyWithHook($value)
    {
        $this->propertyWithHook = 'something else';
    }

    public function changeFoo($value)
    {
        $this->foo = $value;
    }

    public function changeArrayPropertyOne($value)
    {
        $this->arrayProperty[1] = $value;
    }

    public function removeArrayPropertyOne()
    {
        unset($this->arrayProperty[1]);
    }

    public function hasInjections(UrlGenerator $generator, $bar)
    {
        $this->foo = $generator->to('/');
        $this->bar = $bar;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
