<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Tests\TestComponent;
use Livewire\Livewire;

class DataBindingUnitTest extends \Tests\TestCase
{
    public function test_update_component_data()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->set('foo', 'bar');

        $this->assertEquals('bar', $component->foo);
    }

    public function test_update_nested_component_data_inside_array()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->set('foo', []);
        $component->set('foo.0', 'bar');
        $component->set('foo.bar', 'baz');

        $this->assertEquals(['bar', 'bar' => 'baz'], $component->foo);
    }

    public function test_can_remove_an_array_from_an_array()
    {
        Livewire::test(new class extends TestComponent {
            public $tasks = [
                [ 'id' => 123 ],
                [ 'id' => 456 ],
            ];
        })
        // We can simulate Livewire's removing an item from an array
        // by hardcoding "__rm__"...
        ->set('tasks.1', '__rm__')
        ->assertSetStrict('tasks', [['id' => 123]])
        ;
    }
}

class DataBindingStub extends TestComponent
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
}
