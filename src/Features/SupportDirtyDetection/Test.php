<?php

namespace Livewire\Features\SupportDirtyDetection;

use Livewire\Component;
use Livewire\Livewire;

class Test extends \Tests\TestCase
{
    /** @test */
    public function property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action()
    {
        $this->markTestSkipped(); // @todo: Reenable this failing test
        $component = Livewire::test(DirtyDataBindingStub::class);

        $component->set('foo', 'bar');

        $this->assertEquals('bar', $component->foo);
        $this->assertEmpty($component->effects['dirty'] ?? []);

        $component->call('changeFoo', 'baz');

        $this->assertEquals('baz', $component->foo);
        $this->assertContains('foo', $component->effects['dirty']);
    }

    /** @test */
    public function nested_property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action()
    {
        $this->markTestSkipped(); // @todo: Reenable this failing test
        $component = Livewire::test(DirtyDataBindingStub::class);

        $component->set('arrayProperty.1', 'baz');

        $this->assertEquals(['foo', 'baz'], $component->arrayProperty);
        $this->assertEmpty($component->effects['dirty'] ?? []);

        $component->call('changeArrayPropertyOne', 'bar');

        $this->assertEquals(['foo', 'bar'], $component->arrayProperty);
        $this->assertContains('arrayProperty.1', $component->effects['dirty']);
    }

    /** @test */
    public function nested_property_is_marked_as_dirty_if_removed_as_side_effect_of_an_action()
    {
        $component = Livewire::test(DirtyDataBindingStub::class);

        $component->call('removeArrayPropertyOne');

        $this->assertEquals(['foo'], $component->arrayProperty);
        $this->assertContains('arrayProperty.1', $component->effects['dirty']);
    }

    /** @test */
    public function property_is_marked_as_dirty_if_changed_as_side_effect_of_an_action_even_if_the_action_is_data_binding_for_that_specific_property()
    {
        $component = Livewire::test(DirtyDataBindingStub::class);

        $component->set('propertyWithHook', 'something');

        $this->assertEquals('something else', $component->propertyWithHook);
        $this->assertContains('propertyWithHook', $component->effects['dirty']);
    }
}

class DirtyDataBindingStub extends Component
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

    public function render()
    {
        return app('view')->make('null-view');
    }
}
